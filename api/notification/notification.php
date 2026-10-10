<?php


declare(strict_types=1);

require_once dirname(__DIR__)  . '/../app/init.php';
require_once dirname(__DIR__) . '/../app/config/database.php';

use App\middleware\AdminAuthMiddleware;
use App\Repositories\NotificationRepositories\NotificationRepository;
use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Services\NotificationServices\NotificationQueueService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Require an authenticated admin before processing the request.
AdminAuthMiddleware::handleApi();

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');

        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'code'   => 405,
            'message' => 'Method not allowed.'
        ]);
        exit;
    }

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    $department = $input['department'] ?? '';

    if (!is_string($department)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'A valid department is required.'
        ]);
        exit;
    }

    $department = trim($department);

    if (!in_array($department, ['shs', 'college'], true)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid department.'
        ]);
        exit;
    }


    // Initialize repositories using the PDO connection from init.php.
    $notificationRepository = new NotificationRepository($pdo);
    $analyticsRepository = new AnalyticsRepository($pdo);
    $evaluationRepository = new EvaluationRepository($pdo);


    // Initialize the service.
    $notificationQueueService = new NotificationQueueService(
        $notificationRepository,
        $analyticsRepository,
        $evaluationRepository
    );

    $result = $notificationQueueService->queueReminders($department);

    http_response_code(200);

    echo json_encode([
        'status' => 'success',
        'message' => 'Notification queue processed successfully.',
        'data' => $result
    ]);

} catch (\Throwable $e) {

    error_log(
        '[Notification Queue API] ' .
        get_class($e) . ': ' . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
