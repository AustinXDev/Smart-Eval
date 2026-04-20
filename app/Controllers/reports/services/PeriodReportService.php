<?php

require_once __DIR__ . '/../repositories/EvaluationRepository.php';

class PeriodReportService
{
    private $repo;

    public function __construct()
    {
        global $pdo; // assuming you use global PDO
        $this->repo = new EvaluationRepository($pdo);
    }

    public function generate($params)
    {
        $periodId = $params['period_id'] ?? null;

        if (!$periodId) {
            die("Missing period_id");
        }

        // 1. Get period data
        $period = $this->repo->getPeriod($periodId);

        if (!$period) {
            die("Period not found");
        }

        // 2. Get rankings
        $rankings = $this->repo->getFacultyRankings($periodId);

        // 3. Get category breakdown (NOW INCLUDED)
        $categories = $this->repo->getCategoryBreakdown($periodId);

        return [
            'view' => 'period_report.php',
            'data' => [
                'period'     => $period,
                'rankings'   => $rankings,
                'categories' => $categories,
                'rating'     => $this->getRating($period['final_average'])
            ]
        ];
    }

    private function getRating($score)
    {
        if ($score >= 4.5) return ['text' => 'Excellent', 'color' => '#28a745'];
        if ($score >= 3.5) return ['text' => 'Very Good', 'color' => '#fd7e14'];
        if ($score >= 2.5) return ['text' => 'Good', 'color' => '#ffc107'];
        return ['text' => 'Fair', 'color' => '#dc3545'];
    }
}

?>