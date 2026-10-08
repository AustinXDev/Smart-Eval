<?php


require_once __DIR__ . '/../../app/init.php';
require_once __DIR__ . '/../../app/config/database.php';

use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Services\EvaluationServices\EvaluationService;
use App\Controllers\evaluation\EvaluationController;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    $evaluationRepo = new EvaluationRepository($pdo);

    $service = new EvaluationService($evaluationRepo, $pdo);

    $controller = new EvaluationController($service);

    $data = $controller->getEvaluationSummary();

    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'data' => $data
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
