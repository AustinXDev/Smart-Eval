<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\reports\ReportController;
use App\Services\ReportServices\ReportService;
use App\Services\AnalyticsServices\AnalyticsService;
use App\Services\TeacherServices\TeacherReportService;
use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\ArchiveRepo\ArchiveAnalyticsRepository;
use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Repositories\ArchiveRepo\ArchiveTeacherReportRepository;
use App\Repositories\ReportRepositories\TeacherReportRepository as LiveTeacherReportRepository;
use App\Resolvers\AnalyticsResolver;
use App\helpers\AnalyticsHelpers;
use PDO;

class Container
{
    public function __construct(
        private PDO $livePdo,
        private PDO $archivePdo
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
        // Live repositories
        $analyticsRepo = new AnalyticsRepository($this->livePdo);
        $evaluationRepo = new EvaluationRepository($this->livePdo);

        // Archive repositories
        $archiveAnalyticsRepo = new ArchiveAnalyticsRepository(
            $this->archivePdo
        );

        $archiveReadRepo = new ArchiveReadRepository(
            $this->archivePdo
        );

        $archiveTeacherReportRepo = new ArchiveTeacherReportRepository(
            $this->archivePdo
        );

        $helper = new AnalyticsHelpers();

        // Supports live and archived analytics.
        $analyticsService = new AnalyticsService(
            $analyticsRepo,
            new AnalyticsResolver($evaluationRepo),
            $helper,
            $archiveAnalyticsRepo,
            $archiveReadRepo
        );

        // Supports live and archived teacher reports.
        $teacherReportService = new TeacherReportService(
            new LiveTeacherReportRepository($this->livePdo),
            $helper,
            $archiveTeacherReportRepo,
            $archiveReadRepo
        );

        return new ReportService(
            $analyticsRepo,
            $analyticsService,
            $teacherReportService
        );
    }
    public function archiveManagementController(): \App\Controllers\Archive\ArchiveManagementController
    {
        return new \App\Controllers\Archive\ArchiveManagementController(
            new \App\Services\ArchiveServices\ArchiveManagementService(
                new ArchiveReadRepository($this->archivePdo),
                new \App\Repositories\AdminRepo\AdminRepository($this->livePdo),
                $this->archivePdo
            )
        );
    }
}
