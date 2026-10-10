<?php


declare(strict_types=1);

namespace App\Services\NotificationServices;

use App\Repositories\NotificationRepositories\NotificationRepository;
use Closure;
use Throwable;

final class NotificationWorkerService
{
    public function __construct(
        private NotificationRepository $repository,
        private Closure $isPeriodOpen,
        private Closure $stillNeedsEvaluation,
        private Closure $sendEmail
    ) {
    }

    public function run(
        int $batchSize = 10
    ): array {

        $this->repository->recoverStaleJobs(10);
        $jobs = $this->repository->claimBatch($batchSize);

        // Exit early when there are no jobs to process.
        if (empty($jobs)) {
            return [
                'claimed' => 0,
                'sent'    => 0,
                'skipped' => 0,
                'failed'  => 0,
            ];
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($jobs as $job) {

            $id = (int) $job['notification_id'];
            $studentId = (string) $job['student_id'];
            $periodId = (int) $job['period_id'];
            $attemptCount = (int) $job['attempt_count'];

            try {

                if (!(($this->isPeriodOpen)($periodId))) {
                    // Do not send a reminder for a closed or inactive period.
                    $this->repository->markSkipped($id);
                    $skipped++;
                    continue;
                }

                $eligible = ($this->stillNeedsEvaluation)(
                    $studentId,
                    $periodId
                );

                if (!$eligible) {
                    $this->repository->markSent($id);
                    $skipped++;
                    continue;
                }

                ($this->sendEmail)(
                    $studentId,
                    $periodId,
                    (string) $job['type']
                );

                $this->repository->markSent($id);
                $sent++;

            } catch (Throwable $e) {

                error_log(sprintf(
                    '[NotificationWorker] job=%d attempt=%d error=%s',
                    $id,
                    $attemptCount,
                    $e->getMessage()
                ));

                $this->repository->markFailed(
                    $id,
                    $attemptCount,
                    'Email processing failed. See private application logs.'
                );

                $failed++;

            }

        }

        return [
            'claimed' => count($jobs),
            'sent'    => $sent,
            'skipped' => $skipped,
            'failed'  => $failed,
        ];

    }

}
