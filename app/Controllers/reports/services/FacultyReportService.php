<?php

require_once __DIR__ . '/../repositories/EvaluationRepository.php';

class FacultyReportService
{
    public function generate($params)
    {
        $repo = new EvaluationRepository();

        $periodId = $params['period_id'];

        return [
            'view' => 'faculty_report.php',
            'data' => [
                'rankings' => $repo->getFacultyRankings($periodId),
                'summary'  => $repo->getPeriodSummary($periodId)
            ]
        ];
    }
}

?>