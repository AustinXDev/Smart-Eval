<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    $department = trim(
        $_GET['department'] ?? ''
    );

    require_once __DIR__ . '/../../app/config/database.php';

    $studentRepo = new StudentRepository($pdo);

    $service = new StudentService(
        $studentRepo
    );

    $controller = new StudentController(
        $service
    );

    $result = $controller->getAllByDepartment(
        $department
    );

    echo json_encode([
        'status' => 'success',
        ...$result
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}