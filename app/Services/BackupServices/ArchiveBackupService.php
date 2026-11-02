<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use PDO;
use RuntimeException;
use Throwable;

final class ArchiveBackupService
{
    public function __construct(
        private PDO $archivePdo,
        private DatabaseExportService $exportService,
        private BackupEncryptionService $encryptionService,
        private GoogleDriveService $googleDriveService
    ) {
    }

    public function backupPeriod(
        int $periodId,
        ?string $folderId = null
    ): array {
        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }

        $job = $this->getArchiveJob($periodId);

        if (!$job) {
            throw new RuntimeException(
                "Archive job for period {$periodId} not found."
            );
        }

        if ($job['status'] === 'verified') {
            throw new RuntimeException(
                'Archive backup is already verified.'
            );
        }

        if ($job['status'] === 'processing') {
            throw new RuntimeException(
                'Archive backup is already processing.'
            );
        }

        $this->updateJobStatus($periodId, 'processing');

        $exportPath = null;
        $encryptedPath = null;

        try {
            // Export archived database records
            $export = $this->exportService->exportPeriod(
                $periodId
            );

            $exportPath = $export['file_path'];

            // Encrypt the JSON export
            $encrypted = $this->encryptionService->encryptFile(
                $exportPath
            );

            $encryptedPath = $encrypted['file_path'];

            // Upload encrypted backup
            $fileId = $this->googleDriveService->upload(
                $encryptedPath,
                $encrypted['file_name'],
                $folderId
            );

            //Download uploaded file
            $downloadedData =
                $this->googleDriveService->download($fileId);

            // Verify remote SHA-256 checksum
            $remoteChecksum = hash(
                'sha256',
                $downloadedData
            );

            $localChecksum =
                $encrypted['checksum_sha256'];

            if (!hash_equals(
                $localChecksum,
                $remoteChecksum
            )) {
                throw new RuntimeException(
                    'Google Drive backup checksum mismatch.'
                );
            }

            // Save verified backup metadata
            $this->markVerified(
                $periodId,
                $fileId,
                $localChecksum,
                $export['manifest']
            );

            return [
                'period_id' => $periodId,
                'status' => 'verified',
                'google_drive_file_id' => $fileId,
                'checksum_sha256' => $localChecksum,
                'manifest' => $export['manifest']
            ];

        } catch (Throwable $e) {
            $this->markFailed(
                $periodId,
                $e->getMessage()
            );

            throw $e;

        } finally {
            // Remove temporary plaintext export.
            if (
                $exportPath !== null &&
                is_file($exportPath)
            ) {
                @unlink($exportPath);
            }

            // Remove local encrypted copy after upload.
            if (
                $encryptedPath !== null &&
                is_file($encryptedPath)
            ) {
                @unlink($encryptedPath);
            }
        }
    }

    private function getArchiveJob(int $periodId): array|false
    {
        $stmt = $this->archivePdo->prepare(
            'SELECT *
             FROM archive_jobs
             WHERE period_id = ?'
        );

        $stmt->execute([$periodId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateJobStatus(
        int $periodId,
        string $status
    ): void {
        $stmt = $this->archivePdo->prepare(
            'UPDATE archive_jobs
             SET status = :status,
                 error_message = NULL
             WHERE period_id = :period_id'
        );

        $stmt->execute([
            'status' => $status,
            'period_id' => $periodId
        ]);
    }

    private function markVerified(
        int $periodId,
        string $fileId,
        string $checksum,
        array $manifest
    ): void {
        $stmt = $this->archivePdo->prepare(
            'UPDATE archive_jobs
             SET status = :status,
                 google_drive_file_id = :file_id,
                 checksum_sha256 = :checksum,
                 manifest_json = :manifest,
                 archived_at = NOW(),
                 error_message = NULL
             WHERE period_id = :period_id'
        );

        $stmt->execute([
            'status' => 'verified',
            'file_id' => $fileId,
            'checksum' => $checksum,
            'manifest' => json_encode(
                $manifest,
                JSON_THROW_ON_ERROR
            ),
            'period_id' => $periodId
        ]);
    }

    private function markFailed(
        int $periodId,
        string $message
    ): void {
        $stmt = $this->archivePdo->prepare(
            'UPDATE archive_jobs
             SET status = :status,
                 error_message = :message
             WHERE period_id = :period_id'
        );

        $stmt->execute([
            'status' => 'failed',
            'message' => $message,
            'period_id' => $periodId
        ]);
    }


    public function verifyExistingBackup(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }

        $job = $this->getArchiveJob($periodId);

        if (!$job) {
            throw new RuntimeException(
                "Archive job for period {$periodId} not found."
            );
        }

        if ($job['status'] !== 'verified') {
            throw new RuntimeException(
                'Archive backup is not verified.'
            );
        }

        $fileId = $job['google_drive_file_id'] ?? null;
        $expectedChecksum = $job['checksum_sha256'] ?? null;

        if (
            !is_string($fileId) ||
            trim($fileId) === '' ||
            !is_string($expectedChecksum) ||
            preg_match('/^[a-f0-9]{64}$/i', $expectedChecksum) !== 1
        ) {
            throw new RuntimeException(
                'Archive backup metadata is incomplete.'
            );
        }

        // Download the existing encrypted backup.
        $downloadedData = $this->googleDriveService->download(
            $fileId
        );

        // Calculate SHA-256 of downloaded encrypted bytes.
        $actualChecksum = hash(
            'sha256',
            $downloadedData
        );

        $checksumValid = hash_equals(
            strtolower($expectedChecksum),
            $actualChecksum
        );

        if (!$checksumValid) {
            throw new RuntimeException(
                'Existing Google Drive backup checksum mismatch.'
            );
        }

        return [
            'period_id' => $periodId,
            'valid' => true,
            'status' => 'verified',
            'google_drive_file_id' => $fileId,
            'checksum_valid' => true,
            'message' => 'Existing Google Drive backup checksum verified.'
        ];
    }
}
