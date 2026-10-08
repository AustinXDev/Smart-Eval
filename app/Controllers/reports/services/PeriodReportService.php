<?php

require_once __DIR__ . '/../repositories/EvaluationRepository.php';
require_once __DIR__ . '/../utils/RatingHelper.php';

class PeriodReportService
{
    private $repo;
    private $ratingHelper;

    public function __construct()
    {
        global $pdo;
        $this->repo = new EvaluationRepository($pdo);
        $this->ratingHelper = new RatingHelper();
    }

    public function generate($params)
    {
        $periodId = $params['period_id'] ?? null;

        if (!$periodId) {
            die("Missing period_id");
        }

        //Get period data
        $period = $this->repo->getPeriod($periodId);

        if (!$period) {
            die("Period not found");
        }

        //Get rankings
        $rankings = $this->repo->getFacultyRankings($periodId);

        //Get category breakdown (NOW INCLUDED)
        $categories = $this->repo->getCategoryBreakdown($periodId);

        return [
            'view' => 'period_report.php',
            'data' => [
                'period'     => $period,
                'rankings'   => $rankings,
                'categories' => $categories,
                'participation_rate' => $this->ratingHelper->participationRating($period['participation_rate']),
                'rating'     => $this->ratingHelper->getRatingCategory($period['final_average'])
            ]
        ];
    }
}

?>