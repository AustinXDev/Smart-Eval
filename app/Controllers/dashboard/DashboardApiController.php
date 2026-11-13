<?php

namespace App\Controllers\dashboard;

use App\Services\DashboardServices\DashboardService;
use InvalidArgumentException;
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

            if (!is_string($department) || !in_array($department, ['college', 'shs'], true)) {
                http_response_code(400);

                echo json_encode([
                    'status' => 'error',
                    'message' => 'A valid department is required.'
                ]);

                return;
            }

            $data = $this->dashboardService->getDashboard($department);

            echo json_encode([
                'status' => 'success',
                'data' => $data
            ], JSON_THROW_ON_ERROR);

        } catch (InvalidArgumentException $e) {

            http_response_code(400);

            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);

        } catch (Throwable $e) {

            http_response_code(500);

            error_log('Dashboard API Error: ' . get_class($e) . ': ' . $e->getMessage());

            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to load dashboard data'
            ]);
        }
    }
}
