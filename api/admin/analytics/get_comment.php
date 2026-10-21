<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\teachers\TeacherCommentController;
use App\Services\TeacherServices\TeacherCommentService;
use App\Repositories\ReportRepositories\TeacherReportRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;

header('Content-Type: application/json; charset=utf-8');

try {

    $reportRepo = new TeacherReportRepository($pdo);
    $evaluationRepo = new EvaluationRepository($pdo);

    $service = new TeacherCommentService(
        $reportRepo,
        $evaluationRepo
    );

    $controller = new TeacherCommentController($service);

    $response = $controller->getComments($_GET);

    http_response_code(200);
    echo json_encode($response);


} catch (InvalidArgumentException $e) {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

} catch (Throwable $e) {
    error_log($e->__toString());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve teacher comments.'
    ]);
}
