<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\questionnaires\QuestionController;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Services\QuestionServices\QuestionService;

header(
    'Content-Type: application/json; charset=utf-8'
);

try {

    require_once __DIR__ . '/../../app/config/database.php';

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        throw new RuntimeException(
            'Invalid request data.'
        );
    }


    $questionRepo = new QuestionRepositories(
        $pdo
    );

    $service = new QuestionService(
        $pdo,
        $questionRepo
    );

    $controller = new QuestionController(
        $service
    );


    $result = $controller->activateQuestion(
        $data
    );


    echo json_encode($result);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
