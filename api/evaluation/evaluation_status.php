<?php


require_once __DIR__ . '/../../app/init.php';

use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Services\EvaluationServices\EvaluationStatusService;
use App\Controllers\evaluation\EvaluationStatusController;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    $student = $_SESSION['student'] ?? $_SESSION;

    if (!$student) {

        http_response_code(401);

        echo json_encode([
            'success' => 'error',
            'message' => 'Unauthorized.',
        ]);

        exit;

    }

    require_once __DIR__ . '/../../app/config/database.php';

    $evaluationRepo = new EvaluationRepository($pdo);
    $evalStatusRepo = new EvaluationStatusRepository($pdo);
    $teacherRepo = new TeacherRepository($pdo);
    $questionRepo = new QuestionRepositories($pdo);

    $service = new EvaluationStatusService($evaluationRepo, $evalStatusRepo, $teacherRepo, $questionRepo);

    $controller = new EvaluationStatusController($service);

    $data = $controller->getEvaluationStatus($student);

    echo json_encode([
      'status' => 'success',
      'data' => $data
    ]);


} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage(),
    ]);

}
