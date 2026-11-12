<?php

namespace App\Services\AnalyticsServices;

use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Repositories\ArchiveRepo\ArchiveAnalyticsRepository;
use App\Resolvers\AnalyticsResolver;
use App\helpers\AnalyticsHelpers;

class AnalyticsService
{
    public function __construct(
        private AnalyticsRepository $analyticsRepo,
        private AnalyticsResolver $resolver,
        private AnalyticsHelpers $helper,
        private ArchiveAnalyticsRepository $archiveAnalyticsRepo,
        private ArchiveReadRepository $archiveReadRepo
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


        // Resolve the period from the live database.
        $period = $this->resolver->resolvePeriod(
            $department,
            $periodId
        );

        // Validate the resolved period.
        if (
            $period !== null &&
            (
                $period['target_dept'] !== $department ||
                (
                    $periodId !== null &&
                    (int) $period['period_id'] !== $periodId
                )
            )
        ) {
            $period = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Explicit period requested
        |--------------------------------------------------------------------------
        */
        if ($periodId !== null) {

            // Check the archive only if the live period
            // is missing or already closed.
            if ($period === null || (bool) $period['is_closed']) {

                if ($this->archiveReadRepo->isPeriodArchived($periodId)) {

                    $archivedPeriod = $this->archiveReadRepo
                        ->getArchivedPeriod($periodId);

                    if (
                        !$archivedPeriod ||
                        $archivedPeriod['target_dept'] !== $department ||
                        (int) $archivedPeriod['is_closed'] !== 1
                    ) {
                        throw new \RuntimeException(
                            "Archived evaluation data is unavailable or invalid."
                        );
                    }

                    return $this->getArchivedAnalyticsBundle(
                        $archivedPeriod
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Department-only request
        |--------------------------------------------------------------------------
        */
        if ($periodId === null) {

            // Active live evaluation always has priority.
            $isActive = $period !== null
                && !(bool) $period['is_closed'];

            if (!$isActive) {

                // This repository method must return the latest
                // successfully archived closed period.
                $latestArchived = $this->archiveReadRepo
                    ->getLatestArchivedPeriod($department);

                if ($latestArchived) {

                    $archiveIsNewer = $period === null
                        || strcmp(
                            $latestArchived['end_date'],
                            $period['end_date']
                        ) > 0
                        || (
                            $period !== null
                            && $latestArchived['end_date'] === $period['end_date']
                            && (int) $latestArchived['period_id']
                                > (int) $period['period_id']
                        );

                    if ($archiveIsNewer) {
                        return $this->getArchivedAnalyticsBundle(
                            $latestArchived
                        );
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Selected live period already archived
        |--------------------------------------------------------------------------
        */
        if (
            $periodId === null &&
            $period !== null &&
            (bool) $period['is_closed']
        ) {

            $selectedId = (int) $period['period_id'];

            if ($this->archiveReadRepo->isPeriodArchived($selectedId)) {

                $archivedPeriod = $this->archiveReadRepo
                    ->getArchivedPeriod($selectedId);

                // Verify archived period integrity.
                if (
                    !$archivedPeriod ||
                    $archivedPeriod['target_dept'] !== $department ||
                    (int) $archivedPeriod['is_closed'] !== 1
                ) {
                    throw new \RuntimeException(
                        "Archived evaluation data is unavailable or invalid."
                    );
                }

                return $this->getArchivedAnalyticsBundle(
                    $archivedPeriod
                );
            }
        }

        // No evaluation exists in either database.
        if ($period === null) {
            return null;
        }



        /**
         |
         | Determine whether historical or live data
         |
         */
        $periodId = (int) $period['period_id'];
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
        $ratingDistribution = array_fill_keys(
            ['Outstanding', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'],
            0
        );
        foreach ($teacherRanking as $teacher) {
            $rating = AnalyticsHelpers::adjectiveRating(
                (float) ($teacher['mean_score'] ?? 0)
            );
            $ratingDistribution[$rating]++;
        }


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

          'rating_distribution' => $ratingDistribution,

          'not_evaluated' => $notEvaluatedList,

          'abandoned' => $abandonedList
        ];


    }


    /**
     * Build analytics bundle using archived data.
     */
    private function getArchivedAnalyticsBundle(
        array $period
    ): array {

        $periodId = (int) $period['period_id'];
        $department = $period['target_dept'];

        // Get archived analytics.
        $funnel = $this->archiveAnalyticsRepo
            ->getArchivedFunnel($periodId);

        $yearLevel = $this->archiveAnalyticsRepo
            ->getArchivedYearLevelAnalytics(
                $periodId,
                $department
            );

        $categoryPerformance = $this->archiveAnalyticsRepo
            ->getArchivedDepartmentCategoryPerformance(
                $periodId,
                $department
            );

        $questionBreakdown = $this->archiveAnalyticsRepo
            ->getArchivedQuestionBreakdown(
                $periodId,
                $department
            );

        $teacherRanking = $this->archiveAnalyticsRepo
            ->getArchivedTeacherRanking(
                $periodId,
                $department
            );
        $ratingDistribution = array_fill_keys(
            ['Outstanding', 'Very Satisfactory', 'Satisfactory', 'Fair', 'Poor'],
            0
        );
        foreach ($teacherRanking as $teacher) {
            $rating = AnalyticsHelpers::adjectiveRating(
                (float) ($teacher['mean_score'] ?? 0)
            );
            $ratingDistribution[$rating]++;
        }

        $notEvaluated = $this->archiveAnalyticsRepo
            ->getArchivedNotEvaluatedList(
                $periodId,
                $department
            );

        $abandoned = $this->archiveAnalyticsRepo
            ->getArchivedAbandonedList(
                $periodId,
                $department
            );

        $trend = $this->archiveAnalyticsRepo
            ->getArchivedPreviousPeriodTrend(
                $department,
                $periodId
            );

        $trend[] = [
            'period_id' => $periodId,
            'label' => $period['academic_year']
                . ' (' . $period['semester'] . ')',
            'score' => $period['final_average'] ?? 0,
            'end_date' => $period['end_date']
        ];

        usort(
            $trend,
            static fn ($a, $b) =>
                strcmp($a['end_date'], $b['end_date'])
        );

        $meanScoreTrend = array_map(
            static fn ($row) => [
                'academic_year' => $row['label'],
                'final_average' => (float) $row['score']
            ],
            $trend
        );

        return [
            'period' => $period,
            'is_closed' => true,
            'funnel' => $funnel,
            'mean_score_trend' => $meanScoreTrend,
            'year_participation' => $yearLevel,
            'category_performance' => $categoryPerformance,

            'category_highlights' => $this->helper
                ->getDepartmentPerformanceHighlights(
                    $categoryPerformance
                ),

            'question_breakdown' => $this->helper
                ->getQuestionPerformanceHighlights(
                    $questionBreakdown
                ),

            'teacher_ranking' => $teacherRanking,

            'rating_distribution' => $ratingDistribution,
            'not_evaluated' => $notEvaluated,
            'abandoned' => $abandoned
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
                static fn ($a, $b) =>
                strcmp($a['end_date'], $b['end_date'])
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
