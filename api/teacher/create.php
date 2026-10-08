<?php 
require_once __DIR__ . '/../../app/init.php';

use App\Controllers\teachers\TeacherController;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherService;
use App\helpers\FileUploader;

header(
  'Content-type: application/json; charset=utf-8'
);

try {

  require_once __DIR__ . '/../../app/config/database.php';

  $teacherRepo = new TeacherRepository($pdo);

  $uploadDirectory = ROOT_PATH . '/public/uploads/teachers/';

  $fileUploader = new FileUploader(
    $uploadDirectory
  );

  $service = new TeacherService(
    $pdo,
    $teacherRepo,
    $fileUploader
  );

  $controller = new TeacherController($service);

  $result = $controller->create(
    $_POST,
    $_FILES['photo'] ?? null
  );

  echo json_encode([
    'status' => 'success',
    'message' => 'Teacher added successfully.',
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