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

    $questionRepo = new QuestionRepositories($pdo);

    $service = new QuestionService($pdo, $questionRepo);

    $controller = new QuestionController($service);

    $questionSets = $controller->getAllActive();

    echo json_encode([
      'status' => 'success',
      'data' => $questionSets
    ]);

} catch (\Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
