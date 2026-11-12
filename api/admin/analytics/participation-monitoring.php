<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once __DIR__ . '/../../../app/config/database.php';

use App\Controllers\evaluationMonitoring\ParticipationMonitoringController;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\MonitoringRepo\ParticipationMonitoringRepository;
use App\Services\MonitoringServices\ParticipationMonitoringService;

header('Content-Type: application/json; charset=utf-8');

try {
    $controller = new ParticipationMonitoringController(
        new ParticipationMonitoringService(
            new EvaluationRepository($pdo),
            new ParticipationMonitoringRepository($pdo)
        )
    );

    echo json_encode($controller->index($_GET), JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[Participation monitoring] ' . get_class($e) . ': ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unable to load monitoring data.']);
}
