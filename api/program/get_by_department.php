<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\ProgramRepo\ProgramRepository;
use App\Services\ProgramServices\ProgramService;
use App\Controllers\programs\ProgramController;

header('Content-Type: application/json; charset=utf-8');

try {

  $department = trim($_GET['department'] ?? '');

  require_once __DIR__ . '/../../app/config/database.php';

  $programRepo = new ProgramRepository($pdo);

  $service = new ProgramService($programRepo);

  $controller = new ProgramController($service);

  $programs = $controller->getProgramByDepartment(
    $department
  );
  
  echo json_encode([
    'status' => 'error',
    'programs' => $programs
  ]);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);
}

?>