<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';



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

    // Keep the legacy response for other callers; the table opts into paging.
    $result = isset($_GET['draw']) || isset($_GET['counts_only'])
        ? $controller->getStudentList($department, $_GET)
        : $controller->getAllByDepartment($department);

    echo json_encode([
        'status' => 'success',
        ...$result
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => \App\middleware\ApiSecurity::publicMessage($e)
    ]);
}
