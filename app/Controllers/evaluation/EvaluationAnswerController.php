<?php

namespace App\Controllers\evaluation;

use App\Services\EvaluationServices\EvaluationAnswerService;

class EvaluationAnswerController
{
    public function __construct(
        private EvaluationAnswerService $service
    ) {
    }


    public function saveAnswers(
        array $data
    ): array {

        return $this->service->submitAnswers($data);

    }

}
