<?php 

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Admin\AdminAuthController;
use App\Repositories\AdminRepository;
use App\Repositories\AdminTwoFactorRepository;
use App\Services\Admin\AdminAuthService;
use App\providers\EmailProvider;

header('Content-type: application/json');

try{

  $input = json_decode(
    file_get_contents('php://input'),
    true
  ) ?? [];

  $username = trim(
      $input['admin_username'] ?? ''
  );

  $password =
      $input['admin_password'] ?? '';

  $ip =
      $_SERVER['REMOTE_ADDR'] ?? '';

  require_once __DIR__ .
      '/../../../app/config/database.php';

  /**
   * Repositories
   */
  $admin = new AdminRepository($pdo);

  $twoFactor = new AdminTwoFactorRepository($pdo);

  /**
   * Provider
   */

  $mailer = new EmailProvider();

  /**
   * Service
   */

  $auth = new AdminAuthService(
    $admin,
    $twoFactor,
    $mailer
  );

  /**
   * Controller
   */
  $controller = new AdminAuthController(
    $auth
  );

  /**
   * Login
   */

  $response = $controller->login(
    $username,
    $password,
    $ip
  );

  http_response_code(200);

  echo json_encode($response);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
  ]);

}

?>