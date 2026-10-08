<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\questionnaires\QuestionController;
use App\Services\QuestionServices\QuestionService;
use App\Repositories\QuestionRepo\QuestionRepositories;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    $data = [
      'set_id' => $_GET['id'] ?? null
    ];

    require_once __DIR__ . '/../../app/config/database.php';

    $questionRepo = new QuestionRepositories($pdo);

    $service = new QuestionService($pdo, $questionRepo);

    $controller = new QuestionController($service);

    $questions = $controller->getQuestionsBySetId($data);

    echo json_encode([
      'status' => 'success',
      'data' => $questions
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
