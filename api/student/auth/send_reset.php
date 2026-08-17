<?php

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\ForgotPassword\ForgotPasswordController;
use App\providers\EmailProvider;
use App\Repositories\PasswordResetRepository;
use App\Repositories\StudentRepository;
use App\Services\ForgotPasswordServices\ForgotPasswordServices;

header('Content-type: application/json');

//database config
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];


$studentRepo = new StudentRepository($pdo);

$resetRepo = new PasswordResetRepository($pdo);

$mailer = new  EmailProvider();

$service =new ForgotPasswordServices(
  $studentRepo,
  $resetRepo,
  $mailer
);

$controller = new ForgotPasswordController($service);

$response = $controller->handle($input);

http_response_code(
  $response['status'] === 'success' ? 200 : 400
);

echo json_encode($response);

?>