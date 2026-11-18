<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

use App\Controllers\ActivityLogs\ActivityLogController;
use App\Repositories\LogRepo\ActivityLogRepository;

header('Content-Type: application/json; charset=utf-8');

try {

    $controller = new ActivityLogController(new ActivityLogRepository($pdo));

    echo json_encode(
        ['status' => 'success', ...$controller->list($_GET)],
        JSON_INVALID_UTF8_SUBSTITUTE
    );

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => \App\middleware\ApiSecurity::publicMessage($e),
    ]);
}
