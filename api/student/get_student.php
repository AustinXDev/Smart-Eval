<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header('Content-type: application/json; charset=utf-8');

try{

  $studentId = trim($_GET['student_id'] ?? '');

  require_once __DIR__ . '/../../app/config/database.php';

  $studentRepo = new StudentRepository($pdo);

  $service = new StudentService($studentRepo);

  $controller = new StudentController($service);

  $student = $controller->getById(
    $studentId
  );

  echo json_encode([
    'status' => 'success',
    'student' => $student->toArray()
  ]);

} catch (\Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);

}
?>