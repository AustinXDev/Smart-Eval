<?php

namespace App\Controllers\periods;

use App\Models\EvaluationPeriod;
use App\Services\EvaluationServices\EvaluationService;

class EvaluationController
{
    public function __construct(
        private EvaluationService $service
    ) {
    }

    public function getPeriodById(
        int $periodId
    ): EvaluationPeriod {

        return $this->service->getPeriodById(
            $periodId
        );

    }


    public function getDashboardData(): array
    {

        return [
            'all_periods' => $this->getAllPeriods(),
            'active_periods' => $this->getActivePeriodWithStudentStats()
        ];

    }


    public function getAllPeriods(): array
    {

        $periods = $this->service->getAllPeriods();

        return array_map(
            fn (EvaluationPeriod $period) => $period->toArray(),
            $periods
        );

    }


    public function getActivePeriodWithStudentStats(): array
    {

        return $this->service->getActivePeriodWithStudentStats();

    }


    public function create(
        array $data
    ): EvaluationPeriod {

        return $this->service->create(
            $data
        );

    }


    public function update(
        array $data
    ): EvaluationPeriod {

        return $this->service->update(
            $data
        );

    }


    public function delete(
        array $data
    ): void {

        $periodId = (int) (
            $data['period_id'] ?? 0
        );

        $this->service->delete($periodId);

    }


    public function forceActivate(
        array $data
    ): EvaluationPeriod {

        $periodId = (int) ($data['period_id'] ?? 0);

        return $this->service->forceActivate($periodId);

    }

    public function forceClosed(
        int $periodId
    ): array {

        return $this->service->forceClose($periodId);

    }


    public function runAutomaticUpdate(): void
    {

        $this->service->runAutomaticUpdate();

    }

}
