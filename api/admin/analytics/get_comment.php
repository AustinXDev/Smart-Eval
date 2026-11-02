<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../../app/init.php';

require_once __DIR__ . '/../../../app/config/archive-database.php';

use App\Controllers\teachers\TeacherCommentController;
use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Services\TeacherServices\TeacherReportService;
use App\Repositories\ReportRepositories\TeacherReportRepository;
use App\Repositories\ArchiveRepo\ArchiveTeacherReportRepository;
use App\helpers\AnalyticsHelpers;

header('Content-Type: application/json; charset=utf-8');

try {

    $reportRepo = new TeacherReportRepository($pdo);

    $readRepo = new ArchiveReadRepository($archivePdo);

    $archiveReportRepo = new ArchiveTeacherReportRepository($archivePdo);


    $service = new TeacherReportService(
        $reportRepo,
        new AnalyticsHelpers(),
        $archiveReportRepo,
        $readRepo
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
