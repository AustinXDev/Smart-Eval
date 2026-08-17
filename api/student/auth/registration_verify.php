<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Register\RegistrationVerificationController;
use App\Controllers\TwoFactor\TwoFactorController;
use App\Repositories\StudentRepository;
use App\Repositories\TwoFactorRepository;
use App\Services\RegistrationService\RegistrationVerificationService;
use App\Services\TwoFactorServices\TwoFactorService;
use App\providers\EmailProvider;
use App\Services\RegistrationService\RegistrationService;

header('Content-type: application/json');

try {

$rawInput = file_get_contents('php://input');

error_log(
    'REGISTRATION VERIFY RAW: ' . $rawInput
);

$input = json_decode(
    $rawInput,
    true
) ?? [];

error_log(
    'REGISTRATION VERIFY INPUT: ' .
    print_r($input, true)
);

$studentId = trim(
    $input['student_id'] ?? ''
);

$code = trim(
    $input['code'] ?? ''
);

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

  require_once __DIR__ . '/../../app/config/database.php';

  /**
   * Repositories
   */

  $students = new StudentRepository($pdo);

  $twoFactorRepo = new TwoFactorRepository($pdo);

  /**
   * Email
   */
  $mailer = new EmailProvider();

  /**
   * two factor Service
   */
  $twoFactor = new TwoFactorService(
    $students,
    $twoFactorRepo,
    $mailer
  );

  /**
   * Registration verification services
   */
  $verification = new RegistrationVerificationService(
    $students,
    $twoFactor
  );

  /**
   * Registration verification controller
   */
  $controller = new RegistrationVerificationController(
    $verification
  );

  /**
   * Verify registration OTP
   */
  $response = $controller->verify(
    $studentId,
    $code,
    $ip
  );

  http_response_code(200);

  echo json_encode($response);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status'  => 'error',
    "message" => $e->getMessage()
  ]);

}


?>