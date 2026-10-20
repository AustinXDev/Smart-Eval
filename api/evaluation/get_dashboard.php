<?php




require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../app/init.php';

use App\Services\EvaluationServices\EvaluationService;
use App\Controllers\periods\EvaluationController;
use App\Repositories\EvaluationRepo\EvaluationRepository;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    require_once __DIR__ . '/../../app/config/database.php';

    $repository = new EvaluationRepository($pdo);

    $service = new EvaluationService($repository, $pdo);

    $controller = new EvaluationController($service);

    $result = ($_GET['cards_only'] ?? '') === '1'
        ? ['status' => 'success', 'active_periods' => $controller->getActivePeriodWithStudentStats()]
        : $controller->getDashboardData();

    echo json_encode($result);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => \App\middleware\ApiSecurity::publicMessage($e),
      ]);

}
