<?php 

use App\Repositories\AdminRepository;
use App\Repositories\AdminPasswordResetRepository;
use App\Services\Admin\PasswordResetServices\AdminPasswordResetService;
use App\Controllers\Admin\AdminForgotPasswordController;
use App\providers\EmailProvider;

header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];


  $username = trim(
    $input['admin_username'] ?? ''
  );


  require_once __DIR__ . '/../../../app/config/database.php';


  /**
   * Repositories
   */
  $admin = new AdminRepository($pdo);

  $resetRepo = new AdminPasswordResetRepository($pdo);

  /**
   * Providers
   */
  $mailer = new EmailProvider();


  /**
   * Service
   */
  $service = new AdminPasswordResetService(
    $admin,
    $resetRepo,
    $mailer
  );


  /**
   * Controller
   */
  $controller = new AdminForgotPasswordController($service);


  /**
   * Send Reset Link
   */
  $response = $controller->handle(
    $username
  );

  http_response_code(200);

  echo json_encode($response);

} catch (Throwable $e) {
  
  http_response_code(400);

  echo json_encode([
    'status'  => 'error',
    'message' => $e->getMessage()
  ]);

}
?>