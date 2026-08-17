<?php 

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Admin\AdminAuthController;
use App\Repositories\AdminRepository;
use App\Repositories\AdminTwoFactorRepository;
use App\Services\Admin\AdminAuthService;
use App\providers\EmailProvider;


header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];

  $code = trim($input['code'] ?? '');

  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  require_once __DIR__ . '/../../../app/config/database.php';


  /**
   * Repositories
   */
  $adminRepo = new AdminRepository($pdo);

  $adminTwoFactorRepo = new AdminTwoFactorRepository($pdo);


  /**
   * Mailer
   */
  $mailer = new EmailProvider();


  /**
   * Services
   */
  $authService = new AdminAuthService(
    $adminRepo,
    $adminTwoFactorRepo,
    $mailer
  );


  /**
   * Controller
   */
  $controller = new AdminAuthController(
    $authService
  );

  /**
   * Verify OTP
   */
  $response = $controller->verify(
    $code,
    $ip
  );

  /**
   * Success
   */
  http_response_code(200);

  echo json_encode($response);
  
} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status'  => 'error',
    'message' =>  $e->getMessage()
  ]);

}

?>