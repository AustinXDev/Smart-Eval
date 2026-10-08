<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\teachers\TeacherController;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherService;
use App\helpers\FileUploader;

header('Content-Type: application/json; charset: utf-8');

try {
  
  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];

  $teacherId = (int) ($input['teacher_id'] ?? 0);


  require_once ROOT_PATH . '/app/config/database.php';

  $teacherRepo = new TeacherRepository($pdo);

  $uploadDirectory = ROOT_PATH . '/public/uploads/teachers';

  $fileUploader = new FileUploader(
      $uploadDirectory
  );

  $service = new TeacherService($pdo, $teacherRepo, $fileUploader);

  $controller = new TeacherController($service);

  $result = $controller->delete($teacherId);

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