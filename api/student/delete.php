<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  );

  $studentId = trim($input['student_id'] ?? '');
  $force = $input['force'] ?? 0;

  require_once __DIR__ . '/../../app/config/database.php';

  $studentRepo = new StudentRepository($pdo);

  $service = new StudentService($studentRepo);

  $controller = new StudentController($service);

  $response = $controller->delete($studentId, $force);

  http_response_code(200);

  echo json_encode($response);


} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode($e->getMessage());

}

?>