<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Services\BackupServices\ArchiveBackupService;
use App\Repositories\ArchiveRepo\ArchiveRepository;
use Throwable;
use RuntimeException;

final class ArchiveAutomationService
{
    public function __construct(
        private ArchiveRepository $archiveRepo,
        private ArchiveService $archiveService,
        private ArchiveBackupService $backupService,
        private ArchiveCleanupService $cleanupService,
        private ArchiveReadinessService $readinessService
    ) {
    }

    public function run(?string $folderId = null): array
    {
        echo "[INFO] Checking eligible evaluation periods..." . PHP_EOL;

        $periods = $this->archiveRepo->findEligiblePeriods();

        $summary = [
            'eligible' => count($periods),
            'completed' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        echo "[INFO] Eligible periods found: "
            . $summary['eligible']
            . PHP_EOL;

        if ($summary['eligible'] === 0) {
            echo "[INFO] No periods require processing." . PHP_EOL;

            return $summary;
        }

        foreach ($periods as $period) {

            $periodId = (int) $period['period_id'];

            try {

                echo "[PERIOD {$periodId}] Checking..." . PHP_EOL;

                // Check teacher notification readiness.
                $readiness = $this->readinessService->check(
                    $periodId
                );

                if (!$readiness['ready']) {

                    echo "[SKIPPED] Period {$periodId}: "
                        . "Teacher notifications not completed."
                        . PHP_EOL;

                    $summary['skipped']++;
                    continue;
                }

                // Find existing archive job.
                $job = $this->archiveRepo->findArchiveJob(
                    $periodId
                );

                // Archive if no existing job.
                if ($job === null) {

                    echo "[ARCHIVE] Period {$periodId}: "
                        . "Copying evaluation records..."
                        . PHP_EOL;

                    $this->archiveService->archivePeriod(
                        $periodId,
                        false
                    );

                    $job = $this->archiveRepo->findArchiveJob(
                        $periodId
                    );

                    if ($job === null) {
                        throw new RuntimeException(
                            'Archive job was not created.'
                        );
                    }
                }

                // Upload encrypted backup when pending.
                if ($job['status'] === 'pending') {

                    echo "[BACKUP] Period {$periodId}: "
                        . "Uploading encrypted backup..."
                        . PHP_EOL;

                    $this->backupService->backupPeriod(
                        $periodId,
                        $folderId
                    );

                    $job = $this->archiveRepo->findArchiveJob(
                        $periodId
                    );
                }

                // Ensure backup verification succeeded.
                if (
                    $job === null ||
                    $job['status'] !== 'verified'
                ) {

                    echo "[SKIPPED] Period {$periodId}: "
                        . "Backup is not verified."
                        . PHP_EOL;

                    $summary['skipped']++;
                    continue;
                }

                // Perform cleanup only when pending.
                if (
                    $job['cleanup_status'] === 'pending' &&
                    $job['cleaned_at'] === null
                ) {

                    echo "[CLEANUP] Period {$periodId}: "
                        . "Starting live database cleanup..."
                        . PHP_EOL;

                    $result = $this->cleanupService
                        ->cleanupPeriod($periodId, false);

                    // Confirm the database state after cleanup.
                    $updatedJob = $this->archiveRepo
                        ->findArchiveJob($periodId);

                    if (
                        $updatedJob === null ||
                        $updatedJob['cleanup_status'] !== 'completed' ||
                        $updatedJob['cleaned_at'] === null
                    ) {
                        throw new RuntimeException(
                            'Cleanup did not reach completed status.'
                        );
                    }

                    echo "[SUCCESS] Period {$periodId}: "
                        . ($result['message'] ?? 'Cleanup completed.')
                        . PHP_EOL;

                    $summary['completed']++;

                } else {

                    echo "[SKIPPED] Period {$periodId}: "
                        . "Cleanup status is "
                        . $job['cleanup_status']
                        . PHP_EOL;

                    $summary['skipped']++;
                }

            } catch (Throwable $e) {

                $summary['failed']++;

                error_log(
                    "[ARCHIVE WORKER] Period {$periodId}: "
                    . $e->getMessage()
                );

                echo "[FAILED] Period {$periodId}: "
                    . $e->getMessage()
                    . PHP_EOL;
            }
        }

        echo "[SUMMARY] Eligible: {$summary['eligible']}"
            . " | Completed: {$summary['completed']}"
            . " | Skipped: {$summary['skipped']}"
            . " | Failed: {$summary['failed']}"
            . PHP_EOL;

        return $summary;
    }
}
