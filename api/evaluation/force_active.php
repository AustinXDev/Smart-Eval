<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\periods\EvaluationController;
use App\Services\EvaluationServices\EvaluationService;
use App\Repositories\EvaluationRepo\EvaluationRepository;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    require_once __DIR__ . '/../../app/config/database.php';


    $repository = new EvaluationRepository($pdo);

    $service = new EvaluationService($repository, $pdo);

    $controller = new EvaluationController($service);

    $period = $controller->forceActivate($data);

    echo json_encode([
      'status' => 'success',
      'message' => 'Period force-activated successfully.',
      'period' => $period->toArray()
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
