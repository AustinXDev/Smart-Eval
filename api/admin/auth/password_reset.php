<?php 

/**
 * Repositories
 */
use App\Repositories\AdminRepository;
use App\Repositories\AdminResetPasswordRepository;


/**
 * Services
 */
use App\Services\Admin\ResetPasswordServices\ResetPasswordService;


/**
 * Controller
 */
use App\Controllers\Admin\AdminResetPasswordController;


header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];

  require_once __DIR__ . '/../../../app/config/database.php';

  $adminRepo = new AdminRepository($pdo);

  $adminResetRepo = new AdminResetPasswordRepository($pdo);

  $service = new ResetPasswordService(
    $adminRepo,
    $adminResetRepo
  );

  $controller = new AdminResetPasswordController($service);

  $response = $controller->handle($input);

  http_response_code(200);

  echo json_encode($response);

} catch (\Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status'  =>  'error',
    'message' =>  $e->getMessage() 
  ]);

}

?>