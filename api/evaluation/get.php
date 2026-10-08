<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\periods\EvaluationController;
use App\Services\EvaluationServices\EvaluationService;
use App\Repositories\EvaluationRepo\EvaluationRepository;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    $periodId = (int) ($_GET['period_id'] ?? 0);

    require_once __DIR__ . '/../../app/config/database.php';

    $evaluationRepo = new EvaluationRepository($pdo);

    $service = new EvaluationService(
        $evaluationRepo,
        $pdo
    );

    $controller = new EvaluationController(
        $service
    );

    $period = $controller->getPeriodById(
        $periodId
    );

    echo json_encode([
        'status' => 'success',
        'period' => $period->toArray()
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
