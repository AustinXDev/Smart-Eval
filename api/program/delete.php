<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\programs\ProgramController;
use App\Services\ProgramServices\ProgramService;
use App\Repositories\ProgramRepo\ProgramRepository;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    require_once __DIR__ . '/../../app/config/database.php';

    $programRepo = new ProgramRepository($pdo);

    $service = new ProgramService($pdo, $programRepo);

    $controller = new ProgramController($service);

    $deleted = $controller->delete($data);

    echo json_encode([
      'status' => 'success',
      'message' => 'Program successfully deleted.',
      'data' => $deleted
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
