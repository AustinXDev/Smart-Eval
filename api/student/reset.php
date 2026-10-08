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

  $studentId =  trim($input['student_id'] ?? '');

  require_once __DIR__ . '/../../app/config/database.php';

  $StudentRepo = new StudentRepository($pdo);

  $service = new StudentService($StudentRepo);

  $controller = new StudentController($service);

  $repsonse = $controller->resetPassword($studentId);

  http_response_code(200);

  echo json_encode($repsonse);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);

}

?>