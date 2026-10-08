<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\questionnaires\QuestionController;
use App\Services\QuestionServices\QuestionService;
use App\Repositories\QuestionRepo\QuestionRepositories;

header(
    "Content-Type: application/json; charset=utf-8 "
);

try {

    $data = $_POST;

    require_once __DIR__ . '/../../app/config/database.php';

    $questionRepo = new QuestionRepositories($pdo);

    $service = new QuestionService($pdo, $questionRepo);

    $controller = new QuestionController($service);

    $result = $controller->updateSet($data);

    echo json_encode([
      'status' => 'success',
      'message' => 'Set name updated successfully.',
      'data' => $result
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
