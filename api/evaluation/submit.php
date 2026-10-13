<?php




require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\EvaluationRepo\EvaluationAnswerRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Services\EvaluationServices\EvaluationAnswerService;
use App\Services\EvaluationServices\EvaluationStatusService;
use App\Controllers\evaluation\EvaluationAnswerController;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    require_once __DIR__ . '/../../app/config/database.php';

    $evalAnswerRepo = new EvaluationAnswerRepository($pdo);
    $evalRepo = new EvaluationRepository($pdo);
    $evalStatusRepo = new EvaluationStatusRepository($pdo);
    $teacherRepo = new TeacherRepository($pdo);
    $questionRepo = new QuestionRepositories($pdo);


    $evalStatusService = new EvaluationStatusService($evalRepo, $evalStatusRepo, $teacherRepo, $questionRepo);

    $evalAnswerService = new EvaluationAnswerService($evalAnswerRepo, $evalStatusService, $pdo);

    $controller = new EvaluationAnswerController($evalAnswerService);

    $result = $controller->saveAnswers($data, $_SESSION['student']['student_id']);

    echo json_encode($result);

} catch (\App\Services\EvaluationServices\EvaluationRequestException $e) {
    http_response_code($e->status);
    echo json_encode(['status' => 'error', 'message' => \App\middleware\ApiSecurity::publicMessage($e)]);
} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => \App\middleware\ApiSecurity::publicMessage($e)
    ]);

}
