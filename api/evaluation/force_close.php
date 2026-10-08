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

    $periodId = $data['period_id'] ?? null;

    require_once __DIR__ . '/../../app/config/database.php';

    $repository = new EvaluationRepository($pdo);

    $service = new EvaluationService($repository, $pdo);

    $controller = new EvaluationController($service);

    $result = $controller->forceClosed($periodId);

    echo json_encode([
      'status' => 'success',
      'message' =>
          'Evaluation period successfully closed. '
          . 'Participation: '
          . number_format(
              $result['paticipation_rate'],
              2
          )
          . '%'
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage(),
    ]);

}
