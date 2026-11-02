<?php

declare(strict_types=1);

namespace App\Controllers\reportAnalytics;

use App\Services\AnalyticsServices\AnalyticsService;
use RuntimeException;

class AnalyticsController
{
    public function __construct(
        private AnalyticsService $service
    ) {
    }

    public function getBundle(): ?array
    {
        $department = trim(
            (string) ($_GET['dept'] ?? '')
        );

        if ($department === '') {
            http_response_code(400);

            throw new RuntimeException(
                'Invalid analytics department.'
            );
        }

        // Optional period_id.
        // Missing or empty means automatic period resolution.
        $rawPeriodId = $_GET['period_id'] ?? null;

        $periodId = null;

        if ($rawPeriodId !== null && $rawPeriodId !== '') {

            $validatedId = filter_var(
                $rawPeriodId,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($validatedId === false) {
                http_response_code(400);

                throw new RuntimeException(
                    'Invalid evaluation period ID.'
                );
            }

            $periodId = $validatedId;
        }

        return $this->service->getAnalyticsBundle(
            $department,
            $periodId
        );
    }
}
