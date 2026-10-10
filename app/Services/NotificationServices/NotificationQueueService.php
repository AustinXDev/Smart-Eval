<?php


declare(strict_types=1);

namespace App\Services\NotificationServices;

use App\Repositories\NotificationRepositories\NotificationRepository;
use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use InvalidArgumentException;
use RuntimeException;

final class NotificationQueueService
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private AnalyticsRepository $analyticRepository,
        private EvaluationRepository $evaluationRepository
    ) {
    }


    /**
     *@param string $department Students who still need to evaluate.
     */
    public function queueReminders(
        string $department
    ): array {

        $department = trim($department);

        if ($department === '') {
            throw new InvalidArgumentException(
                'A valid department is required.'
            );
        }

        $period = $this->evaluationRepository->findActiveByDepartment($department);

        if (!$period) {
            throw new RuntimeException(
                'No active evaluation period was found for this department.'
            );
        }

        $periodId = (int) $period['period_id'];

        // Confirm that the period is currently open.
        if (!$this->evaluationRepository->isPeriodOpen($periodId)) {
            throw new RuntimeException(
                'The evaluation period is not currently open.'
            );
        }

        $notEvaluted = $this->analyticRepository->getLiveNotEvaluatedList((int) $periodId, (string) $department);

        $incomplete = $this->analyticRepository->getLiveAbandonedList((int) $periodId, (string) $department);

        $students = [];

        foreach (array_merge($notEvaluted, $incomplete) as $student) {
            $students[$student['student_id']] = $student;
        }

        $queued    = 0;

        foreach ($students as $student) {

            $this->notificationRepository->enqueue(
                $periodId,
                $student['student_id'],
                'evaluation_reminder'
            );

            $queued++;

        }

        return [
          'eligible'  => count($students),
          'processed' => $queued,
        ];

    }

}
