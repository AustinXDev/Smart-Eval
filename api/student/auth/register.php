<?php 
require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Register\RegisterController;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\RegistrationService\RegistrationService;
use App\providers\EmailProvider;

header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$studentRepository = new StudentRepository($pdo);
$mailer = new EmailProvider();

$service = new RegistrationService(
  $studentRepository, 
  $mailer
);

$controller = new RegisterController($service);

echo json_encode($controller->handle($input));

?>