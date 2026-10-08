<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherLoadService;
use App\Controllers\teachers\TeacherLoadController;

header(
  'Content-Type: application/json; charset=utf-8'
);

try {

  require_once __DIR__ . '/../../app/config/database.php';

  $input = $_POST ?? null;
  

  $teacherRepo = new TeacherRepository($pdo);

  $service = new TeacherLoadService($teacherRepo);

  $Controller = new TeacherLoadController($service);

  $result = $Controller->create(
    $input
  );

  echo json_encode([
    'status' => 'success',
    'data' => $result
  ]);

} catch (\Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);

}

?>