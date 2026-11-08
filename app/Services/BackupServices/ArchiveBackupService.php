<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use PDO;
use RuntimeException;
use Throwable;

final class ArchiveBackupService
{
    // Maximum encrypted backup size: 20 MiB.
    private const MAX_DOWNLOAD_BYTES = 20 * 1024 * 1024;

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
            // Export archive records to JSON.
            $export = $this->exportService->exportPeriod(
                $periodId
            );

            $exportPath = $export['file_path'];

            // Encrypt JSON backup.
            $encrypted = $this->encryptionService->encryptFile(
                $exportPath
            );

            $encryptedPath = $encrypted['file_path'];

            // Upload encrypted backup to Google Drive.
            $fileId = $this->googleDriveService->upload(
                $encryptedPath,
                $encrypted['file_name'],
                $folderId
            );

            // Download uploaded backup for verification.
            $downloadedData =
                $this->googleDriveService->download($fileId);

            $this->assertChecksum(
                $downloadedData,
                $encrypted['checksum_sha256']
            );

            // Save verified backup metadata.
            $this->markVerified(
                $periodId,
                $fileId,
                $encrypted['checksum_sha256'],
                $export['manifest']
            );

            return [
                'period_id' => $periodId,
                'status' => 'verified',
                'google_drive_file_id' => $fileId,
                'checksum_sha256' =>
                    $encrypted['checksum_sha256'],
                'manifest' => $export['manifest']
            ];

        } catch (Throwable $e) {
            $this->markFailed(
                $periodId,
                $e->getMessage()
            );

            throw $e;

        } finally {
            foreach ([$exportPath, $encryptedPath] as $path) {
                if (
                    $path === null ||
                    !is_file($path)
                ) {
                    continue;
                }

                if (!unlink($path)) {
                    error_log(
                        '[ARCHIVE BACKUP] Unable to delete '
                        . 'temporary backup file: ' . $path
                    );
                }
            }
        }
    }

    /**
     * Download a verified encrypted backup from Google Drive.
     *
     * Works even when evaluation records have been removed
     * from the archive database, provided archive_jobs
     * and the Google Drive backup were preserved.
     *
     * Returns encrypted bytes, not decrypted JSON.
     */
    public function downloadVerifiedBackup(
        int $periodId
    ): string {
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
        $checksum = $job['checksum_sha256'] ?? null;

        if (
            !is_string($fileId) ||
            trim($fileId) === '' ||
            !is_string($checksum) ||
            preg_match(
                '/^[a-f0-9]{64}$/i',
                $checksum
            ) !== 1
        ) {
            throw new RuntimeException(
                'Archive backup metadata is incomplete.'
            );
        }

        $downloadedData =
            $this->googleDriveService->download($fileId);

        $this->assertChecksum(
            $downloadedData,
            $checksum
        );

        return $downloadedData;
    }

    /**
     * Verify an existing Google Drive backup.
     *
     * Does not change archive_jobs status or
     * modify archived evaluation records.
     */
    public function verifyExistingBackup(
        int $periodId
    ): array {
        $this->downloadVerifiedBackup($periodId);

        $job = $this->getArchiveJob($periodId);

        return [
            'period_id' => $periodId,
            'valid' => true,
            'status' => 'verified',
            'google_drive_file_id' =>
                $job['google_drive_file_id'],
            'checksum_valid' => true,
            'message' =>
                'Existing Google Drive backup checksum verified.'
        ];
    }

    /**
     * Verify encrypted backup size and SHA-256 checksum.
     */
    private function assertChecksum(
        string $encryptedData,
        string $expectedChecksum
    ): void {
        $size = strlen($encryptedData);

        if (
            $size === 0 ||
            $size > self::MAX_DOWNLOAD_BYTES
        ) {
            throw new RuntimeException(
                'Encrypted backup exceeds the allowed size '
                . 'or is empty.'
            );
        }

        if (
            preg_match(
                '/^[a-f0-9]{64}$/i',
                $expectedChecksum
            ) !== 1
        ) {
            throw new RuntimeException(
                'Invalid backup checksum metadata.'
            );
        }

        $actualChecksum = hash(
            'sha256',
            $encryptedData
        );

        if (!hash_equals(
            strtolower($expectedChecksum),
            $actualChecksum
        )) {
            throw new RuntimeException(
                'Google Drive backup checksum mismatch.'
            );
        }
    }

    private function getArchiveJob(
        int $periodId
    ): array|false {
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
}
