<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once __DIR__ . '/../../../app/init.php';
require_once __DIR__ . '/../../../app/config/database.php';

use App\Controllers\dashboard\DashboardApiController;
use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\DashboardRepo\DashboardRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\MonitoringRepo\ParticipationMonitoringRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\DashboardServices\DashboardService;

$service = new DashboardService(
    new EvaluationRepository($pdo),
    new StudentRepository($pdo),
    new TeacherRepository($pdo),
    new EvaluationStatusRepository($pdo),
    new ParticipationMonitoringRepository($pdo),
    new AnalyticsRepository($pdo),
    new DashboardRepository($pdo)
);

(new DashboardApiController($service))->bundle();
