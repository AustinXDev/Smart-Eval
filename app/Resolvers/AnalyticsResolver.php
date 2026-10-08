<?php

namespace App\Resolvers;

use App\Repositories\EvaluationRepo\EvaluationRepository;

final class AnalyticsResolver
{
    public function __construct(
        private EvaluationRepository $evalRepo
    ) {
    }

    public function resolvePeriod(
        string $department,
        ?int $periodId
    ): ?array {

        /*
           |--------------------------------------------------------------------------
           | Explicit period requested
           |--------------------------------------------------------------------------
           */

        if ($periodId !== null && $periodId > 0) {

            return $this->evalRepo
                ->getById($periodId);
        }


        /*
        |--------------------------------------------------------------------------
        | No period_id
        |
        | First try the currently active evaluation.
        |--------------------------------------------------------------------------
        */

        $activePeriod = $this->evalRepo
            ->findActiveByDepartment($department);

        if ($activePeriod !== null) {
            return $activePeriod;
        }


        /*
        |--------------------------------------------------------------------------
        | No active evaluation
        |
        | Fall back to the most recent closed evaluation.
        |--------------------------------------------------------------------------
        */
        return $this->evalRepo->getLatestClosedPeriod($department);


    }

}
