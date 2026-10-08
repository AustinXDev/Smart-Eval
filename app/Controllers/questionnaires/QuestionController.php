<?php

namespace App\Controllers\questionnaires;

use App\Models\QuestionModel;
use App\Services\QuestionServices\QuestionService;

class QuestionController
{
    public function __construct(
        private QuestionService $service
    ) {
    }

    public function create(
        array $data
    ): array {

        $questionSet = $this->service->create($data);

        return [
          'set_id' => $questionSet->setId,
          'set_name' => $questionSet->setName,
          'is_active' => $questionSet->isActive
        ];

    }


    public function updateSet(
        array $data
    ): array {

        $questionSet = $this->service->updateSet($data);

        return[
          'set_id' => $questionSet->setId,
          'set_name' => $questionSet->setName,
          'is_active' => $questionSet->isActive
        ];

    }


    public function delete(
        array $data
    ): array {

        return $this->service->deleteSet(
            $data
        );

    }


    public function getAllActive(): array
    {

        return $this->service->getAllActive();

    }


    public function getActiveSet(): array
    {

        $sets = $this->service->getActiveSet();

        return array_map(
            fn (QuestionModel $set): array => $set->toArray(),
            $sets
        );

    }


    public function getQuestionsBySetId(
        array $data
    ): array {

        return $this->service->getQuestionById($data);

    }


    public function createQuestion(
        array $data
    ): array {

        return $this->service->createQuestion($data);
    }


    public function updateQuestion(
        array $data
    ): array {

        return $this->service->updateQuestion($data);
    }


    public function activateQuestion(
        array $data
    ): array {

        return $this->service->activateQuestion($data);
    }


    public function deleteQuestion(
        array $data
    ): array {

        return $this->service->deleteQuestion($data);
    }
}
