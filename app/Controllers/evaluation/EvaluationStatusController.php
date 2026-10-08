<?php

namespace App\Controllers\evaluation;

use App\Services\EvaluationServices\EvaluationStatusService;

class EvaluationStatusController
{
    public function __construct(
        private EvaluationStatusService $service
    ) {
    }


    public function getEvaluationStatus(
        array $student
    ): array {

        return
        $this->service->getEvaluation($student);

    }

}
