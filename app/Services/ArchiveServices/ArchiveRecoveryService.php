<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Services\BackupServices\BackupEncryptionService;
use App\Services\BackupServices\DatabaseRestoreService;
use Closure;
use PDO;
use RuntimeException;
use Throwable;

final class ArchiveRecoveryService
{
    private const MAX_UPLOAD_BYTES = 20 * 1024 * 1024; // Maximum encrypted backup size: 20 MiB.

    public function __construct(
        private PDO $archivePdo,
        private BackupEncryptionService $encryptionService,
        private DatabaseRestoreService $restoreService,
        private Closure $validatePayload,
        private string $archiveDatabase
    ) {

        $this->archivePdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $selectedDatabase = $this->archivePdo
            ->query('SELECT DATABASE()')
            ->fetchColumn();

        if (
            !is_string($selectedDatabase) ||
            $selectedDatabase !== $archiveDatabase
        ) {
            throw new RuntimeException(
                'Recovery database configuration mismatch.'
            );
        }

    }

    public function restoreUploadedBackup(
        array $uploadedFile,
        int $expectedPeriodId,
        int $adminId
    ): array {

        if ($expectedPeriodId <= 0 || $adminId <= 0) {
            throw new RuntimeException(
                'Invalid recovery request.'
            );
        }

        $sourcePath = $this->validateUpload($uploadedFile);

        $encryptedPath = null;
        $jsonPath = null;
        $lockAcquired = false;
        $operationId = null;
        $committed = false;

        $lockName = sprintf(
            'smarteval:archive-period:%s:%d',
            $this->archiveDatabase,
            $expectedPeriodId
        );

        try {

            // Obtain exclusive recovery lock.
            $stmt = $this->archivePdo->prepare(
                'SELECT GET_LOCK(?, 10)'
            );

            $stmt->execute([$lockName]);

            if ((int) $stmt->fetchColumn() !== 1) {
                throw new RuntimeException(
                    'Another archive operation is running.'
                );
            }

            $lockAcquired = true;

            // Check registered archive metadata.
            $job = $this->getArchiveJob(
                $expectedPeriodId
            );

            if ($job['status'] !== 'verified') {
                throw new RuntimeException(
                    'Archive backup is not verified.'
                );
            }

            if ($job['archive_data_status'] !== 'deleted') {
                throw new RuntimeException(
                    'This period is not eligible for recovery.'
                );
            }

            // Verify uploaded file against original checksum.
            $checksum = hash_file(
                'sha256',
                $sourcePath
            );

            if (
                $checksum === false ||
                !hash_equals(
                    strtolower((string) $job['checksum_sha256']),
                    strtolower($checksum)
                )
            ) {
                throw new RuntimeException(
                    'Uploaded file does not match the registered backup.'
                );
            }

            // Copy upload to private working files.
            [$encryptedPath, $jsonPath] =
                $this->createTemporaryPaths();

            if (!copy($sourcePath, $encryptedPath)) {
                throw new RuntimeException(
                    'Unable to prepare uploaded backup.'
                );
            }

            @chmod($encryptedPath, 0600);

            // Decrypt.
            $this->encryptionService->decryptFile(
                $encryptedPath,
                $jsonPath
            );

            // Validate JSON, manifest and records.
            $payload = $this->readBackup($jsonPath);

            $periodId = filter_var(
                $payload['manifest']['period_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($periodId !== $expectedPeriodId) {
                throw new RuntimeException(
                    'Backup period does not match selected period.'
                );
            }

            ($this->validatePayload)($payload);

            // Log attempt outside restore transaction.
            $operationId = $this->createOperation(
                $expectedPeriodId,
                $adminId
            );

            $this->archivePdo->beginTransaction();

            try {
                // Recheck eligibility while holding row lock.
                $job = $this->getArchiveJob(
                    $expectedPeriodId,
                    true
                );

                if (
                    $job['status'] !== 'verified' ||
                    $job['archive_data_status'] !== 'deleted'
                ) {
                    throw new RuntimeException(
                        'Archive recovery eligibility changed.'
                    );
                }

                // Restore all 11 archived tables.
                $result = $this->restoreService
                    ->restoreWithinTransaction(
                        $jsonPath
                    );

                $counts = $result['record_counts'] ?? null;

                if (!is_array($counts)) {
                    throw new RuntimeException(
                        'Restore service did not return record counts.'
                    );
                }

                $total = array_sum($counts);

                $stmt = $this->archivePdo->prepare("
                    UPDATE archive_jobs
                    SET archive_data_status = 'available'
                    WHERE period_id = :period_id
                      AND status = 'verified'
                      AND archive_data_status = 'deleted'
                ");

                $stmt->execute([
                    'period_id' => $expectedPeriodId
                ]);

                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Unable to update archive availability.'
                    );
                }

                // Finalize audit within the same transaction.
                $this->completeOperation(
                    $operationId,
                    $total
                );

                $this->archivePdo->commit();
                $committed = true;

                return [
                    'operation_id' => $operationId,
                    'period_id' => $expectedPeriodId,
                    'status' => 'completed',
                    'records_restored' => $total,
                    'record_counts' => $counts
                ];

            } catch (Throwable $e) {
                if ($this->archivePdo->inTransaction()) {
                    $this->archivePdo->rollBack();
                }

                throw $e;
            }

        } catch (Throwable $e) {
            if (
                $operationId !== null &&
                !$committed
            ) {
                try {
                    $this->failOperation(
                        $operationId,
                        $e->getMessage()
                    );
                } catch (Throwable $logError) {
                    error_log(
                        '[ARCHIVE RECOVERY] Audit logging failed: '
                        . $logError->getMessage()
                    );
                }
            }

            throw $e;

        } finally {
            if ($lockAcquired) {
                try {
                    $stmt = $this->archivePdo->prepare(
                        'SELECT RELEASE_LOCK(?)'
                    );

                    $stmt->execute([$lockName]);
                } catch (Throwable $e) {
                    error_log(
                        '[ARCHIVE RECOVERY] Lock release failed: '
                        . $e->getMessage()
                    );
                }
            }

            foreach ([$encryptedPath, $jsonPath] as $path) {
                if (
                    is_string($path) &&
                    is_file($path)
                ) {
                    @unlink($path);
                }
            }
        }
    }


    private function validateUpload(array $file): string
    {
        if (
            ($file['error'] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_OK
        ) {
            throw new RuntimeException(
                'Backup upload failed.'
            );
        }

        $name = $file['name'] ?? '';
        $path = $file['tmp_name'] ?? '';
        $size = $file['size'] ?? 0;

        if (
            !is_string($name) ||
            !str_ends_with(strtolower($name), '.json.enc') ||
            !is_string($path) ||
            !is_uploaded_file($path) ||
            !is_int($size) ||
            $size < 36 ||
            $size > self::MAX_UPLOAD_BYTES
        ) {
            throw new RuntimeException(
                'Invalid encrypted backup upload.'
            );
        }

        return $path;
    }


    private function createTemporaryPaths(): array
    {
        $base = tempnam(
            sys_get_temp_dir(),
            'smarteval_restore_'
        );

        if ($base === false) {
            throw new RuntimeException(
                'Unable to create temporary backup file.'
            );
        }

        @chmod($base, 0600);

        return [$base, $base . '.json'];
    }


    private function readBackup(string $path): array
    {
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException(
                'Unable to read decrypted backup.'
            );
        }

        $payload = json_decode(
            $json,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($payload)) {
            throw new RuntimeException(
                'Invalid backup JSON structure.'
            );
        }

        return $payload;
    }


    private function getArchiveJob(
        int $periodId,
        bool $forUpdate = false
    ): array {
        $sql = "
            SELECT
                period_id,
                status,
                archive_data_status,
                checksum_sha256
            FROM archive_jobs
            WHERE period_id = :period_id
        ";

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job === false) {
            throw new RuntimeException(
                'Registered archive backup not found.'
            );
        }

        return $job;
    }


    private function createOperation(
        int $periodId,
        int $adminId
    ): int {
        $stmt = $this->archivePdo->prepare("
            INSERT INTO archive_restore_operations (
                period_id,
                admin_id,
                source,
                status
            )
            VALUES (
                :period_id,
                :admin_id,
                'upload',
                'processing'
            )
        ");

        $stmt->execute([
            'period_id' => $periodId,
            'admin_id' => $adminId
        ]);

        return (int) $this->archivePdo->lastInsertId();
    }


    private function completeOperation(
        int $operationId,
        int $total
    ): void {
        $stmt = $this->archivePdo->prepare("
            UPDATE archive_restore_operations
            SET
                status = 'completed',
                records_restored = :total,
                completed_at = NOW()
            WHERE operation_id = :operation_id
              AND status = 'processing'
        ");

        $stmt->execute([
            'total' => $total,
            'operation_id' => $operationId
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to finalize recovery operation.'
            );
        }
    }


    private function failOperation(
        int $operationId,
        string $message
    ): void {
        $stmt = $this->archivePdo->prepare("
            UPDATE archive_restore_operations
            SET
                status = 'failed',
                error_message = :message,
                completed_at = NOW()
            WHERE operation_id = :operation_id
              AND status = 'processing'
        ");

        $stmt->execute([
            'message' => substr($message, 0, 2000),
            'operation_id' => $operationId
        ]);
    }

}
