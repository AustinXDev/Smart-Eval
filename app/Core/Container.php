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

        return new ReportController(
            $this->reportService()
        );

    }

    public function reportService(): ReportService
    {

        $analyticRepository = new AnalyticsRepository($this->pdo);
        $evaluationRepository = new EvaluationRepository($this->pdo);

        $helper = new AnalyticsHelpers();

        $analyticsService = new AnalyticsService(
            $analyticRepository,
            new AnalyticsResolver($evaluationRepository),
            $helper
        );

        $teacherReportService = new TeacherReportService(
            new TeacherReportRepository($this->pdo),
            $helper
        );

        return new ReportService(
            $analyticRepository,
            $analyticsService,
            $teacherReportService
        );

    }

    public function teacherReportExcel(): ReportController
    {

        return new ReportController(
            $this->reportService()
        );

    }

}
