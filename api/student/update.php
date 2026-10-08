<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header('Content-type: application/json');

try{

  $input = $_POST ?? [];

  $oldId = trim($input['old_student_id'] ?? '');
  $newId = trim($input['student_id'] ?? '');
  $fullName = trim($input['full_name'] ?? '');
  $email = trim($input['email'] ?? '');
  $yearLevel = $input['year_level'];
  $programId = $input['program_id'];

  require_once __DIR__ . '/../../app/config/database.php';

  $studentRepo = new StudentRepository($pdo);

  $service = new StudentService($studentRepo);

  $controller = new StudentController($service);

  $response = $controller->update(
    $oldId,
    $newId,
    $fullName,
    $email,
    $programId,
    $yearLevel
  );

  http_response_code(200);

  echo json_encode($response);

} catch (Throwable $e){

  http_response_code(400);

  echo json_encode($e->getMessage());

}

?>