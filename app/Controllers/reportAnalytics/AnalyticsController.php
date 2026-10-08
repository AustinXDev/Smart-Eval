<?php

namespace App\Controllers\reportAnalytics;

use App\Services\AnalyticsServices\AnalyticsService;
use RuntimeException;

class AnalyticsController
{
    public function __construct(
        private AnalyticsService $service
    ) {
    }

    public function getBundle(): array
    {

        $periodId = (int) ($_GET['period_id'] ?? 0);
        $department = trim($_GET['dept'] ?? '');


        if ($department === '') {

            http_response_code(400);

            throw new RuntimeException(
                "Invalid analytics parameter"
            );

        }


        return $this->service->getAnalyticsBundle($department, $periodId);

    }

}
