<?php

namespace App\Controllers\dashboard;

use App\Services\DashboardServices\DashboardService;
use Throwable;

class DashboardApiController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function bundle(): void
    {
        header('Content-Type: application/json');

        try {

            $department = $_GET['department'] ?? '';

            if ($department === '') {
                http_response_code(400);

                echo json_encode([
                    'status' => 'error',
                    'message' => 'Department is required'
                ]);

                return;
            }

            $data = $this->dashboardService
                ->getDashboard($department);

            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);

        } catch (Throwable $e) {

            http_response_code(500);

            error_log(
                'Dashboard API Error: ' . $e->getMessage()
            );

            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to load dashboard data',
                'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
            ]);
        }
    }
}

?>