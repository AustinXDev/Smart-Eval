<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveRepository;
use PDO;
use RuntimeException;
use Throwable;

final class ArchiveCleanupService
{
    public function __construct(
        private PDO $livePdo,
        private ArchiveRepository $archiveRepo,
        private ArchiveEligibilityService $eligibilityService,
        private ArchiveReadinessService $readinessService,
        private ArchiveValidationService $validationService
    ) {
    }

    public function cleanupPeriod(
        int $periodId,
        bool $dryRun = true
    ): array {

        // Validate evaluation period ID.
        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }

        // Check if evaluation period exists.
        $period = $this->archiveRepo->findPeriodById($periodId);

        if (!$period) {
            throw new RuntimeException(
                'Evaluation period not found.'
            );
        }

        // Check seven-day eligibility.
        if (!$this->eligibilityService->isEligible($period)) {
            throw new RuntimeException(
                'Evaluation period is not yet eligible for cleanup.'
            );
        }

        // Check teacher notification readiness.
        $readiness = $this->readinessService->check($periodId);

        if (!$readiness['ready']) {
            throw new RuntimeException(
                'Teacher notifications are not yet completed.'
            );
        }

        // Check archive job.
        $job = $this->archiveRepo->findArchiveJob($periodId);

        if (!$job || $job['status'] !== 'verified') {
            throw new RuntimeException(
                'Archive backup is not verified.'
            );
        }

        // Prevent duplicate cleanup.
        if ($job['cleaned_at'] !== null) {
            throw new RuntimeException(
                'Evaluation period has already been cleaned.'
            );
        }

        // Only pending cleanup jobs can proceed.
        if ($job['cleanup_status'] !== 'pending') {
            throw new RuntimeException(
                'Cleanup is not pending. Current status: ' .
                $job['cleanup_status']
            );
        }

        // Validate archived evaluation contents.
        $contents = $this->validationService
            ->validateContents($periodId);

        if (!$contents['valid']) {
            throw new RuntimeException(
                'Archived evaluation contents failed validation.'
            );
        }

        // Validate snapshot records.
        $snapshots = $this->validationService
            ->validateSnapshots($periodId);

        if (!$snapshots['valid']) {
            throw new RuntimeException(
                'Archived snapshot records failed validation.'
            );
        }

        // Validate participation and notification records.
        $cleanupRecords = $this->validationService
            ->validateCleanupRecords($periodId);

        if (!$cleanupRecords['valid']) {
            throw new RuntimeException(
                'Archived participation or notification records failed validation.'
            );
        }

        // Compare live and archived record counts.
        $tables = [
            'evaluation_status',
            'evaluation_answers',
            'participation_history',
            'notification_queue',
            'teacher_notification_queue'
        ];

        foreach ($tables as $table) {

            $liveCount = $this->archiveRepo->countPeriodRecords(
                $this->archiveRepo->getLiveDatabase(),
                $table,
                $periodId
            );

            $archiveCount = $this->archiveRepo->countPeriodRecords(
                $this->archiveRepo->getArchiveDatabase(),
                $table,
                $periodId
            );

            if ($liveCount !== $archiveCount) {
                throw new RuntimeException(
                    "Archive count mismatch: {$table}."
                );
            }
        }

        // Verify Google Drive backup.
        $backup = $this->validationService
            ->validateBackup($periodId);

        if (!$backup['valid']) {
            throw new RuntimeException(
                'Google Drive backup verification failed.'
            );
        }

        // Return without deleting records during dry run.
        if ($dryRun) {
            return [
                'period_id' => $periodId,
                'dry_run' => true,
                'cleaned' => false,
                'ready_for_cleanup' => true,
                'message' =>
                    'All checks passed. No records were deleted.'
            ];
        }

        // Ensure cleanup owns the database transaction.
        if ($this->livePdo->inTransaction()) {
            throw new RuntimeException(
                'Cleanup requires its own database transaction.'
            );
        }

        // Claim cleanup before deleting records.
        if (!$this->archiveRepo->claimCleanup($periodId)) {
            throw new RuntimeException(
                'Cleanup is already processing or cannot be claimed.'
            );
        }

        // 14. Perform cleanup inside one transaction.
        try {

            $this->livePdo->beginTransaction();

            // Delete evaluation answers first (child records).
            $deletedAnswers = $this->archiveRepo
                ->deleteLiveEvaluationAnswers($periodId);

            // Delete evaluation statuses (parent records).
            $deletedStatuses = $this->archiveRepo
                ->deleteLiveEvaluationStatus($periodId);

            // Delete participation history.
            $deletedParticipation = $this->archiveRepo
                ->deleteLiveParticipationHistory($periodId);

            // Delete student notification records.
            $deletedNotifications = $this->archiveRepo
                ->deleteLiveNotifications($periodId);

            // Delete teacher notification records.
            $deletedTeacherNotifications = $this->archiveRepo
                ->deleteLiveTeacherNotifications($periodId);

            // Mark cleanup completed within the SAME transaction.
            $this->archiveRepo
                ->markPeriodCleanedInTransaction($periodId);

            // Commit all changes together.
            $this->livePdo->commit();

        } catch (Throwable $e) {

            // Roll back uncommitted changes.
            if ($this->livePdo->inTransaction()) {
                try {
                    $this->livePdo->rollBack();
                } catch (Throwable $rollbackError) {
                    error_log(
                        'Cleanup rollback failed: ' .
                        $rollbackError->getMessage()
                    );
                }
            }

            // Flag cleanup for manual reconciliation.
            try {
                $this->archiveRepo
                    ->markCleanupForReconciliation($periodId);

            } catch (Throwable $recoveryError) {

                error_log(
                    'Unable to flag cleanup for reconciliation: ' .
                    $recoveryError->getMessage()
                );
            }

            throw new RuntimeException(
                'Cleanup failed. Manual reconciliation is required.',
                0,
                $e
            );
        }

        // Return successful cleanup result.
        return [
            'period_id' => $periodId,
            'dry_run' => false,
            'cleaned' => true,
            'deleted_answers' => $deletedAnswers,
            'deleted_statuses' => $deletedStatuses,
            'deleted_participation' => $deletedParticipation,
            'deleted_notifications' => $deletedNotifications,
            'deleted_teacher_notifications' => $deletedTeacherNotifications,
            'message' =>
                'Live evaluation records cleaned successfully.'
        ];
    }
}
