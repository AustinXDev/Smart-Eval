<?php


require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\ResetPassword\ResetPasswordController;
use App\Repositories\StudentRepo\ResetPasswordRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\ResetPasswordServices\ResetPasswordServices;

header('Content-type: application/json');

try {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];


    require_once __DIR__ . '/../../../app/config/database.php';

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

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);

}
