<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\questionnaires\QuestionController;
use App\Services\QuestionServices\QuestionService;
use App\Repositories\QuestionRepo\QuestionRepositories;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    require_once __DIR__ . '/../../app/config/database.php';

    $questionRepo = new QuestionRepositories($pdo);

    $service = new QuestionService($pdo, $questionRepo);

    $controller = new QuestionController($service);

    $result = $controller->delete($data);

    if ($result['action'] === 'archived') {

        echo json_encode([
            'status' => 'warning',
            'message' => 'Some questions already have answers. The set was archived instead.',
            'data' => $result
        ]);

        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Set and all its questions deleted successfully.',
        'data' => $result
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
