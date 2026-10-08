<?php

require_once __DIR__ . '/FacultyReportService.php';
require_once __DIR__ . '/TeacherReportService.php';
require_once __DIR__ . '/PeriodReportService.php';

class ReportService
{
    public function handle($type, $params)
    {
        return match ($type) {
            'faculty' => (new FacultyReportService())->generate($params),
            'teacher' => (new TeacherReportService())->generate($params),
            'period'  => (new PeriodReportService())->generate($params),
            default   => throw new Exception("Invalid report type")
        };
    }
}

?>