<?php

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\ForgotPassword\ForgotPasswordController;
use App\providers\EmailProvider;
use App\Repositories\StudentRepo\PasswordResetRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\ForgotPasswordServices\ForgotPasswordServices;

header('Content-type: application/json');

try {

    //database config
    require_once __DIR__ . '/../../../app/config/database.php';

    $input = json_decode(file_get_contents('php://input'), true) ?? [];


    $studentRepo = new StudentRepository($pdo);

    $resetRepo = new PasswordResetRepository($pdo);

    $mailer = new EmailProvider();

    $service = new ForgotPasswordServices(
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

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);

}
