<?php

namespace App\Controllers\teachers;

use App\Services\TeacherServices\TeacherCommentService;
use InvalidArgumentException;

final class TeacherCommentController
{
    public function __construct(
        private TeacherCommentService $service
    ) {
    }

    public function getComments(array $query): array
    {
        $teacherId = filter_var(
            $query['teacher_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $periodId = filter_var(
            $query['period_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($teacherId === false || $teacherId === null || $teacherId <= 0) {
            throw new InvalidArgumentException("Invalid teacher ID.");
        }

        if ($periodId === false || $periodId === null || $periodId <= 0) {
            throw new InvalidArgumentException("Invalid evaluation period ID.");
        }

        $result = $this->service->getCommentList(
            $teacherId,
            $periodId
        );

        return [
            'status' => 'success',
            'data' => $result
        ];
    }

}
