<?php



require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header('Content-type: application/json');

try {

  $input = json_decode(
    file_get_contents('php://input'),
    true
  );

  $studentId =  trim($input['student_id'] ?? '');

  require_once __DIR__ . '/../../app/config/database.php';

  $StudentRepo = new StudentRepository($pdo);

  $recovery = new \App\Services\ForgotPasswordServices\ForgotPasswordServices(
      $StudentRepo,
      new \App\Repositories\StudentRepo\PasswordResetRepository($pdo),
      new \App\providers\EmailProvider()
  );
  $service = new StudentService($StudentRepo, $recovery);

  $controller = new StudentController($service);

  $repsonse = $controller->resetPassword($studentId);

  http_response_code(200);

  echo json_encode($repsonse);

} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => \App\middleware\ApiSecurity::publicMessage($e)
  ]);

}

?>
