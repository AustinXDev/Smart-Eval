<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\programs\ProgramController;
use App\Services\ProgramServices\ProgramService;
use App\Repositories\ProgramRepo\ProgramRepository;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    require_once __DIR__ . '/../../app/config/database.php';

    $programRepo = new ProgramRepository($pdo);

    $service = new ProgramService($pdo, $programRepo);

    $controller = new ProgramController($service);

    $data = $controller->getAllProgram();

    echo json_encode([
      'status' => 'success',
      'data' => $data
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage(),
      'data' => null
    ]);

}
