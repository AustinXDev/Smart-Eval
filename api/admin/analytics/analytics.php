<?php

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../../app/init.php';

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/config/archive-database.php';

use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\ArchiveRepo\ArchiveAnalyticsRepository;
use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Services\AnalyticsServices\AnalyticsService;
use App\Controllers\reportAnalytics\AnalyticsController;
use App\helpers\AnalyticsHelpers;
use App\Resolvers\AnalyticsResolver;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    /**
     * Analytics Dependencies
     */

    // Live database repositories
    $analyticsRepo = new AnalyticsRepository($pdo);
    $evaluationRepo = new EvaluationRepository($pdo);

    // Archive database repositories
    $archiveAnalyticsRepo = new ArchiveAnalyticsRepository($archivePdo);
    $archiveReadRepo = new ArchiveReadRepository($archivePdo);

    //Resolver
    $resolver = new AnalyticsResolver($evaluationRepo);

    //Helper
    $helper = new AnalyticsHelpers();

    //Service
    $service = new AnalyticsService(
        $analyticsRepo,
        $resolver,
        $helper,
        $archiveAnalyticsRepo,
        $archiveReadRepo
    );

    //Controller
    $controller = new AnalyticsController($service);

    $response = $controller->getBundle();

    echo json_encode([
      'status' => 'success',
      'data' => $response
    ]);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status'  => 'error',
      'message' => \App\middleware\ApiSecurity::publicMessage($e),
    ]);

}
