<?php

namespace App\helpers;

final class InsightHelpers
{
    public static function trendWording(
        bool $hasComparison,
        float $currentAverage,
        float $previousAverage
    ): string {

        if (!$hasComparison) {
            return "establishing the baseline benchmark for this academic period";
        }

        $rawDiff = $currentAverage - $previousAverage;
        $isPositive = $rawDiff >= 0;
        $diffFormatted = ReportViewHelpers::number(abs($rawDiff));

        if ($isPositive) {
            return "representing a +{$diffFormatted} point improvement over the previous evaluation cycle";
        }

        return "reflecting a {$diffFormatted} point decline from the previous evaluation cycle";
    }


    public static function teacherPerformanceInsights(
        array $summarizeTeacherPerformance
    ): string {

        $totalTeachers = $summarizeTeacherPerformance['total_evaluated_teachers'];
        $averageScore  = $summarizeTeacherPerformance['average_score'];
        $highestScore  = $summarizeTeacherPerformance['highest_score'];
        $lowestScore   = $summarizeTeacherPerformance['lowest_score'];
        $scoreDiff     = $summarizeTeacherPerformance['score_diff'];
        $ratings       = $summarizeTeacherPerformance['rating_counts'];

        // Avoid division by zero
        if ($totalTeachers === 0) {
            return 'No teacher evaluation data is available for this period.';
        }

        // Find the most common rating
        $dominantRating = array_keys($ratings, max($ratings))[0];
        $dominantCount  = $ratings[$dominantRating];

        $dominantPercentage = ($dominantCount / $totalTeachers) * 100;

        // Count positive ratings
        $positiveRatings =
            $ratings['Outstanding'] +
            $ratings['Very Satisfactory'];

        $positivePercentage = ($positiveRatings / $totalTeachers) * 100;

        // Build the main summary
        $summary = sprintf(
            '<b>%d</b> teachers were evaluated, with an average teacher mean score of <b>%.2f</b>. ' .
            'Scores ranged from <b>%.2f</b> to <b>%.2f</b>, showing a <b>%.2f</b>-point difference between the highest and lowest recorded scores. ',
            $totalTeachers,
            $averageScore,
            $lowestScore,
            $highestScore,
            $scoreDiff
        );

        // Add rating distribution insight
        $summary .= sprintf(
            '<b>%.1f%%</b> of evaluated teachers received either Outstanding or Very Satisfactory ratings. ',
            $positivePercentage
        );

        // Add dominant rating insight
        $summary .= sprintf(
            'The most common rating was %s, accounting for %d of %d evaluated teachers <b>(%.1f%%)</b>. ',
            $dominantRating,
            $dominantCount,
            $totalTeachers,
            $dominantPercentage
        );

        // lower-rating observation
        $lowerRatings =
            $ratings['Satisfactory'] +
            $ratings['Fair'] +
            $ratings['Poor'];

        if ($lowerRatings === 0) {
            $summary .=
                'No teachers received Satisfactory, Fair, or Poor ratings based on the recorded evaluation results.';
        } else {
            $summary .= sprintf(
                '<b>%d</b> teacher(s) received a rating below Very Satisfactory and may warrant further review.',
                $lowerRatings
            );
        }

        return $summary;
    }


    public static function yearParticipationInsights(
        array $year_participation
    ): string {

        if (empty($year_participation)) {
            return 'No year-level participation data is available.';
        }

        $insights = [];

        foreach ($year_participation as $year) {

            $yearLevel = $year['year_level'];

            $enrolled = (int) $year['total_enrolled'];
            $finished = (int) $year['total_finished'];
            $notFinished = (int) $year['total_not_finished'];

            if ($enrolled > 0) {
                $completionRate = ($finished / $enrolled) * 100;
            } else {
                $completionRate = 0;
            }

            $insights[] = sprintf(
                '%s year recorded a %.1f%% completion rate, with %d of %d enrolled students completing the evaluation.',
                $yearLevel,
                $completionRate,
                $finished,
                $enrolled
            );
        }

        return implode(' ', $insights);
    }


    public static function categoryPerformanceInsights(
        array $categoryPerformance
    ): string {

        if (empty($categoryPerformance)) {
            return 'No category performance data is available.';
        }

        $highest = null;
        $lowest = null;

        foreach ($categoryPerformance as $category) {

            $score = (float) $category['average_score'];

            if ($highest === null || $score > $highest['score']) {
                $highest = [
                    'category' => $category['category'],
                    'score' => $score
                ];
            }

            if ($lowest === null || $score < $lowest['score']) {
                $lowest = [
                    'category' => $category['category'],
                    'score' => $score
                ];
            }
        }

        // Check whether multiple categories share the highest score
        $highestCategories = [];

        foreach ($categoryPerformance as $category) {
            if ((float) $category['average_score'] === $highest['score']) {
                $highestCategories[] = $category['category'];
            }
        }

        if (count($highestCategories) > 1) {
            $highestText = implode(', ', $highestCategories);

            return sprintf(
                'Category performance was consistently positive, with %s recording the highest category mean of %.2f. The remaining categories recorded scores of %.2f.',
                $highestText,
                $highest['score'],
                $lowest['score']
            );
        }

        return sprintf(
            '%s recorded the highest category mean at %.2f, while %s recorded the lowest at %.2f.',
            $highest['category'],
            $highest['score'],
            $lowest['category'],
            $lowest['score']
        );
    }


    public static function questionPerformanceInsights(
        array $questionBreakdown
    ): string {

        $strengths = $questionBreakdown['strengths'] ?? [];
        $weaknesses = $questionBreakdown['weaknesses'] ?? [];

        if (empty($strengths) && empty($weaknesses)) {
            return 'No question-level performance data is available.';
        }

        $summary = '';

        if (!empty($strengths)) {

            $topStrength = $strengths[0];

            $summary .= sprintf(
                'The highest-rated question recorded a mean score of %.2f. ',
                (float) $topStrength['score']
            );
        }

        if (!empty($weaknesses)) {

            $lowestQuestion = $weaknesses[0];
            $lowestScore = (float) $lowestQuestion['score'];

            if ($lowestScore >= 4.0) {
                return 'No questions were identified within the defined lower-performance range.';
            }

            $summary .= sprintf(
                'The lowest-rated question recorded a mean score of %.2f and may warrant further review.',
                (float) $lowestQuestion['score']
            );

        } else {

            $summary .=
                'No questions were identified within the defined lower-performance range.';
        }

        return $summary;
    }


}
