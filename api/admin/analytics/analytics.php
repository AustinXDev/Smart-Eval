<?php


require_once __DIR__ . '/../../../app/init.php';

use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
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

    //Repository
    $analyticsRepo = new AnalyticsRepository($pdo);
    $evaluationRepo = new EvaluationRepository($pdo);

    //Resolver
    $resolver = new AnalyticsResolver($evaluationRepo);

    //Helper
    $helper = new AnalyticsHelpers();

    //Service
    $service = new AnalyticsService($analyticsRepo, $resolver, $helper);

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
      'message' => $e->getMessage(),
      'line' => $e->getLine(),
      'file' => $e->getFile()
    ]);

}
