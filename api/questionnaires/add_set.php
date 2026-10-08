<?php


require_once __DIR__ . '/../../app/init.php';

header(
    'Content-Type: application/json; charset=utf-8'
);

use App\Controllers\questionnaires\QuestionController;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Services\QuestionServices\QuestionService;

try {

    $data = $_POST;

    require_once __DIR__ . '/../../app/config/database.php';

    $questionRepo = new QuestionRepositories($pdo);

    $service = new QuestionService($pdo, $questionRepo);

    $controller = new QuestionController($service);

    /**
     * create
     */
    $result = $controller->create($data);


    echo json_encode([
      'status' => 'success',
      'message' => 'Question set successfully created.', 'data' => $result
    ]);


} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
