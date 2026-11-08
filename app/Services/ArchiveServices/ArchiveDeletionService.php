<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;
use Throwable;

final class ArchiveDeletionService
{
    public function __construct(
        private PDO $archivePdo
    ) {
    }

    public function deletePeriod(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period.'
            );
        }

        $this->archivePdo->beginTransaction();

        try {
            // Lock archive metadata to prevent concurrent deletion.
            $stmt = $this->archivePdo->prepare(
                'SELECT *
                 FROM archive_jobs
                 WHERE period_id = :period_id
                 FOR UPDATE'
            );

            $stmt->execute([
                'period_id' => $periodId,
            ]);

            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$job) {
                throw new RuntimeException(
                    'Registered archive backup not found.'
                );
            }

            if ($job['status'] !== 'verified') {
                throw new RuntimeException(
                    'Only verified archives can be deleted.'
                );
            }

            if ($job['archive_data_status'] === 'deleted') {
                throw new RuntimeException(
                    'This archive has already been deleted.'
                );
            }

            if ($job['cleanup_status'] !== 'completed') {
                throw new RuntimeException(
                    'Archive cleanup must be completed before deletion.'
                );
            }

            if (
                empty($job['google_drive_file_id']) ||
                !preg_match(
                    '/^[a-f0-9]{64}$/i',
                    (string) ($job['checksum_sha256'] ?? '')
                )
            ) {
                throw new RuntimeException(
                    'Verified recovery backup metadata is incomplete.'
                );
            }

            if (empty($job['archived_at'])) {
                throw new RuntimeException(
                    'Archive retention date is unavailable.'
                );
            }
            /*
            $timezone = new DateTimeZone('Asia/Manila');

            $archivedAt = new DateTimeImmutable(
                $job['archived_at'],
                $timezone
            );

            $eligibleAt = $archivedAt->modify('+12 months');

            if (new DateTimeImmutable('now', $timezone) < $eligibleAt) {
                throw new RuntimeException(
                    'The 12-month archive retention period has not ended.'
                );
            } */

            // Verify the period is present.
            $stmt = $this->archivePdo->prepare(
                'SELECT period_id
                 FROM evaluation_periods
                 WHERE period_id = :period_id
                 FOR UPDATE'
            );

            $stmt->execute([
                'period_id' => $periodId,
            ]);

            if (!$stmt->fetchColumn()) {
                throw new RuntimeException(
                    'Archived evaluation period does not exist.'
                );
            }

            // Child tables must be deleted before their parents.
            $tables = [
                'evaluation_answers',
                'evaluation_status',
                'participation_history',
                'notification_queue',
                'teacher_notification_queue',
                'question_snapshots',
                'question_set_snapshots',
                'student_snapshots',
                'teacher_snapshots',
                'program_snapshots',
                'evaluation_periods',
            ];

            $deletedCounts = [];
            $totalDeleted = 0;

            foreach ($tables as $table) {
                // Table names are fixed in code, not user input.
                $stmt = $this->archivePdo->prepare(
                    "DELETE FROM `{$table}`
                     WHERE period_id = :period_id"
                );

                $stmt->execute([
                    'period_id' => $periodId,
                ]);

                $count = $stmt->rowCount();

                $deletedCounts[$table] = $count;
                $totalDeleted += $count;
            }

            // Preserve archive_jobs and its recovery metadata.
            $stmt = $this->archivePdo->prepare(
                "UPDATE archive_jobs
                 SET archive_data_status = 'deleted'
                 WHERE period_id = :period_id
                   AND status = 'verified'
                   AND archive_data_status = 'available'"
            );

            $stmt->execute([
                'period_id' => $periodId,
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException(
                    'Unable to update archive deletion status.'
                );
            }

            $this->archivePdo->commit();

            return [
                'period_id' => $periodId,
                'archive_data_status' => 'deleted',
                'backup_retained' => true,
                'records_deleted' => $totalDeleted,
                'deleted_counts' => $deletedCounts,
            ];

        } catch (Throwable $e) {
            if ($this->archivePdo->inTransaction()) {
                $this->archivePdo->rollBack();
            }

            throw $e;
        }
    }
}
