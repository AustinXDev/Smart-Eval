<?php

namespace App\Controllers\evaluation;

use App\Services\EvaluationServices\EvaluationService;

class EvaluationController
{
    public function __construct(
        private EvaluationService $service
    ) {
    }

    public function getEvaluationSummary(): array
    {

        // Session check
        $student = $_SESSION['student'] ?? null;
        if (!$student || empty($student['student_id'])) {
            http_response_code(401);
            return(['success' => false, 'error' => 'Unauthorized']);
        }

        return $this->service->getStudentEvaluationSummary($student);

    }

}
