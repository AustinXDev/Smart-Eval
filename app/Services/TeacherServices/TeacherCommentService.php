<?php

declare(strict_types=1);

namespace App\Services\TeacherServices;

use App\Repositories\ReportRepositories\TeacherReportRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use RuntimeException;

class TeacherCommentService
{
    public function __construct(
        private TeacherReportRepository $teacherReportRepo,
        private EvaluationRepository $evaluationRepo
    ) {
    }

    public function getCommentList(
        int $teacherId,
        int $periodId
    ): array {

        if ($teacherId <= 0) {
            throw new RuntimeException(
                "Invalid teacher ID."
            );
        }

        if ($periodId <= 0) {
            throw new RuntimeException(
                "Invalid evaluation period ID."
            );
        }

        $period = $this->evaluationRepo->findById($periodId);

        if (!$period) {
            throw new RuntimeException(
                "Evaluation period not found."
            );
        }

        $comments = $this->teacherReportRepo->getComment($periodId, $teacherId);

        return [
          'comments' => $comments
        ];

    }

}
