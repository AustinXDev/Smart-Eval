<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\teachers\TeacherController;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherService;
use App\helpers\FileUploader;

header(
  "Content-Type: application/json; charset=utf-8"
);

try {

  require_once ROOT_PATH . '/app/config/database.php';

  $repository = new TeacherRepository($pdo);

  $uploadDirectory = ROOT_PATH . '/public/uploads/teachers'; 

  $fileUploader = new FileUploader($uploadDirectory);

  $service = new TeacherService(
    $pdo,
    $repository,
    $fileUploader
  );

  $controller = new TeacherController($service);

  $teacherId = (int)($_GET['id'] ?? 0);

  $department = trim($_GET['department'] ?? '');


  /**
   * Single teacher
   */
  if($teacherId > 0) {

    $teacher = $controller->getById($teacherId);

    echo json_encode([
      'status'  => 'success',
      'data'    => $teacher
    ]);

    exit;

  }

  /**
   * All teachers
   */
  if($department !== '') {

    $data = $controller->getAll($department);

    echo json_encode([
      'status'  => 'success',
      'data'    => $data
    ]);

    exit;
  }

  throw new RuntimeException(
    "Teacher ID or department is required."
  );

} catch (\Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);

}

?>