<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\teachers\TeacherLoadController;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherLoadService;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];


    require_once ROOT_PATH .
        '/app/config/database.php';


    $teacherRepo =
        new TeacherRepository($pdo);


    $service =
        new TeacherLoadService(
            $teacherRepo
        );


    $controller =
        new TeacherLoadController(
            $service
        );


    $result =
        $controller->delete($input);


    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
