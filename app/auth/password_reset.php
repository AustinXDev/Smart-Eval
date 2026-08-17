<?php 

  require_once __DIR__ . '/../init.php';

  use App\Controllers\ResetPassword\ResetPasswordController;
  use App\Repositories\ResetPasswordRepository;
  use App\Repositories\StudentRepository;
  use App\Services\ResetPasswordServices\ResetPasswordServices;

  require_once __DIR__ . '/../config/database.php';

  header('Content-type: application/json');

  $input = json_decode(
    file_get_contents('php://input'), 
    true
  ) ?? [];

    $studentRepo = new StudentRepository($pdo);

    $resetPasswordRepo = new ResetPasswordRepository($pdo);

    $service = new ResetPasswordServices(
      $studentRepo,
      $resetPasswordRepo
    );

    $controller = new ResetPasswordController($service);

    $response = $controller->handle($input);

    http_response_code(
      $response['status'] === 'success'
        ? 200
        : 400
    );

    echo json_encode($response);

?>