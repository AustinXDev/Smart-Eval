<?php

namespace App\Core;

require_once __DIR__ . '/../config/database.php';

use App\Controllers\reports\ReportController;
use App\Services\ReportServices\ReportService;
use App\Services\AnalyticsServices\AnalyticsService;
use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\ReportRepositories\TeacherReportRepository;
use App\Services\TeacherServices\TeacherReportService;
use App\Resolvers\AnalyticsResolver;
use App\helpers\AnalyticsHelpers;
use PDO;

class Container
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function reportController(): ReportController
    {

        $analyticRepository = new AnalyticsRepository($this->pdo);

        $evalRepository = new EvaluationRepository($this->pdo);

        $resolver = new AnalyticsResolver($evalRepository);

        $helper = new AnalyticsHelpers();

        $service = new AnalyticsService($analyticRepository, $resolver, $helper);

        $teacherReportService = new TeacherReportService(
            (new TeacherReportRepository($this->pdo)),
            (new AnalyticsHelpers())
        );

        $reportService = new ReportService($service, $teacherReportService);

        return new ReportController(
            $reportService
        );

    }

}
