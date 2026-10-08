<?php

namespace App\Services\AnalyticsServices;

use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Resolvers\AnalyticsResolver;
use App\helpers\AnalyticsHelpers;

class AnalyticsService
{
    public function __construct(
        private AnalyticsRepository $analyticsRepo,
        private AnalyticsResolver $resolver,
        private AnalyticsHelpers $helper
    ) {
    }

    /**
     * Get the analytics bundle for a department.
     *
     * Period selection priority:
     *
     * 1. Explicit $periodId
     * 2. Active evaluation period
     * 3. Latest closed evaluation period
     * 4. null if no period exists
     */
    public function getAnalyticsBundle(
        string $department,
        ?int $periodId = null,
    ): ?array {

        $period = $this->resolver->resolvePeriod(
            $department,
            $periodId
        );

        if ($period === null) {
            return null;
        }


        $periodId = (int) $period['period_id'];


        /**
         |
         | Determine whether historical or live data
         |
         */

        $isClosed = (bool) $period['is_closed'];


        /**
         |
         | Get analytics data
         |
         */
        $funnel = $this->getFunnel($periodId, $department, $isClosed);


        $meanScoreTrend = $this->getMeanScoreTrend($periodId, $department, $isClosed);


        $yearLevelAnalytics = $this->getYearLevelAnalytics(
            $periodId,
            $department,
            $isClosed
        );


        $categoryPerformance = $this->getDepartmentCategoryPerformance(
            $periodId,
            $department,
            $isClosed
        );


        $questionBreakdown = $this->getQuestionBreakdown(
            $periodId,
            $department,
            $isClosed
        );


        $teacherRanking = $this->getTeacherRankingList($periodId, $department, $isClosed);


        $notEvaluatedList = $this->getNoEvaluatedList($periodId, $department, $isClosed);


        $abandonedList = $this->getAbandonedList($periodId, $department, $isClosed);

        /*
        |--------------------------------------------------------------------------
        | 5. Build highlights
        |--------------------------------------------------------------------------
        */
        $categoryHighlights = $this->helper->getDepartmentPerformanceHighlights($categoryPerformance);


        $questionHighlights = $this->helper->getQuestionPerformanceHighlights($questionBreakdown);


        return [
          'period' => $period,

          'is_closed' => $isClosed,

          'funnel' => $funnel,

          'mean_score_trend' => $meanScoreTrend,

          'year_participation' => $yearLevelAnalytics,

          'category_performance' => $categoryPerformance,

          'category_highlights' => $categoryHighlights,

          'question_breakdown' => $questionHighlights,

          'teacher_ranking' => $teacherRanking,

          'not_evaluated' => $notEvaluatedList,

          'abandoned' => $abandonedList
        ];


    }


    /**
     * Get funnel depending on period state.
     */
    private function getFunnel(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {

            return $this->analyticsRepo->getHistoricalFunnel($periodId);

        }

        return $this->analyticsRepo->getLiveFunnel($periodId, $department);

    }


    /**
     * Get mean score trend.
     */
    private function getMeanScoreTrend(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        /**
         * Closed period
         *
         * Include previous closed periods + selected historical period.
         */
        if ($isClosed) {

            $trend = $this->analyticsRepo->getPreviousPeriodTrend(
                $department,
                $periodId
            );

            $current = $this->analyticsRepo->getHistoricalMeanScore(
                $periodId
            );

            if ($current) {
                $trend[] = $current;
            }

            usort(
                $trend,
                fn ($a, $b) =>
                    strtotime($a['end_date'])
                    <=> strtotime($b['end_date'])
            );

            return array_map(static fn ($row) => [
                'academic_year' => $row['label'],
                'final_average' => (float) ($row['score'] ?? 0.00)
            ], $trend);
        }

        /**
         * Active period
         *
         * Include previous closed periods + current live period.
         */
        $trend = $this->analyticsRepo->getPreviousPeriodTrend(
            $department,
            $periodId
        );

        $current = $this->analyticsRepo->getLiveMeanScore(
            $periodId
        );

        if ($current) {
            $trend[] = $current;
        }

        usort(
            $trend,
            fn ($a, $b) =>
                strtotime($a['end_date'] ?? 'now')
                <=> strtotime($b['end_date'] ?? 'now')
        );

        return array_map(static fn ($row) => [
            'academic_year' => $row['label'],
            'final_average' => (float) ($row['score'] ?? 0.00)
        ], $trend);
    }


    /**
     * Get year-level analytics
     */
    private function getYearLevelAnalytics(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {

            return $this->analyticsRepo->getHistoricalYearLevelAnalytics($periodId, $department);

        }

        return $this->analyticsRepo->getLiveYearLevelAnalytics($periodId);

    }


    /**
     * Get department category performance.
     *
     * Uses historical participation data for closed periods
     * and live evaluation data for active periods.
     */
    private function getDepartmentCategoryPerformance(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {

            return $this->analyticsRepo->getHistoricalDepartmentCategoryPerformance($periodId, $department);

        }

        return $this->analyticsRepo->getLiveDepartmentCategoryPerformance($periodId, $department);

    }


    /**
   * Get question breakdown depending on period state.
   */
    private function getQuestionBreakdown(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {
            return $this->analyticsRepo
                ->getHistoricalQuestionBreakdown(
                    $periodId,
                    $department
                );
        }

        return $this->analyticsRepo
            ->getLiveQuestionBreakdown(
                $periodId,
                $department
            );
    }


    /**
     * Get teacher ranking list depends on period state.
     */
    private function getTeacherRankingList(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {

            return $this->analyticsRepo
                        ->getHistoricalTeacherRanking($periodId, $department);

        }

        return $this->analyticsRepo
                    ->getLiveTeacherRanking($periodId, $department);

    }


    /**
     * Get student who never started list on period state
     */
    private function getNoEvaluatedList(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {
            return $this->analyticsRepo->getHistoricalNotEvaluatedList($periodId, $department);
        }

        return $this->analyticsRepo->getLiveNotEvaluatedList($periodId, $department);

    }

    private function getAbandonedList(
        int $periodId,
        string $department,
        bool $isClosed
    ): array {

        if ($isClosed) {
            return $this->analyticsRepo->getHistoricalAbandonedList($periodId, $department);
        }

        return $this->analyticsRepo->getLiveAbandonedList($periodId, $department);

    }

}
