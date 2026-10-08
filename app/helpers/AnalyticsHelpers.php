<?php

namespace App\helpers;

class AnalyticsHelpers
{
    public static function adjectiveRating(
        ?float $score
    ): string {

        return match (true) {
            $score >= 4.50 => "Outstanding",
            $score >= 3.50 => "Very Satisfactory",
            $score >= 2.50 => "Satisfactory",
            $score >= 1.50 => "Fair",
            default        => "Poor"
        };

    }


    public function addRating(array $items): array
    {

        foreach ($items as &$item) {

            if (
                isset($item['average_score']) &&
                is_numeric($item['average_score'])
            ) {

                $score = (float) $item['average_score'];

                $item['rating'] = $this->adjectiveRating($score);

            } elseif (
                isset($item['mean_score']) &&
                is_numeric($item['mean_score'])
            ) {

                $score = (float) $item['mean_score'];

                $item['rating'] = $this->adjectiveRating($score);

            }

        }

        unset($item);

        return $items;

    }


    /**
     * Generate department-level performance highlights.
     */
    public function getDepartmentPerformanceHighlights(
        array $categoryPerformance
    ): array {

        if (empty($categoryPerformance)) {
            return [];
        }

        $highest = null;
        $lowest = null;

        foreach ($categoryPerformance as $item) {

            $score = (float) ($item['average_score'] ?? 0);

            if ($highest === null || $score > $highest['score']) {
                $highest = [
                    'category' => $item['category'] ?? '',
                    'score' => $score,
                ];
            }

            if ($score < 3 && ($lowest === null || $score < $lowest['score'])) {
                $lowest = [
                    'category' => $item['category'] ?? '',
                    'score' => $score,
                ];
            }
        }

        return [
            'highest' => $highest,
            'lowest' => $lowest,
        ];
    }


    /**
     * Generate question-level performance highlights.
     */
    public function getQuestionPerformanceHighlights(
        array $questionBreakdown
    ): array {

        if (empty($questionBreakdown)) {
            return [];
        }

        $strengths = [];
        $weaknesses = [];

        foreach ($questionBreakdown as $item) {

            $score = (float) ($item['average_score'] ?? 0);

            $question = [
                'question' => $item['question_text'] ?? '',
                'score' => $score,
            ];

            // Strength
            if ($score >= 3.41) {
                $strengths[] = $question;
            }

            // Weakness
            if ($score < 3.41) {
                $weaknesses[] = $question;
            }
        }

        // Highest strengths first
        usort(
            $strengths,
            fn ($a, $b) => $b['score'] <=> $a['score']
        );

        // Weakest questions first
        usort(
            $weaknesses,
            fn ($a, $b) => $a['score'] <=> $b['score']
        );

        return [
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ];
    }



    private static function calculateGrowthRate(
        array $trend
    ): float {

        if (count($trend) < 2) {
            return 0;
        }

        $current = (float) (
            $trend[count($trend) - 1]['final_average'] ?? 0
        );

        $previous = (float) (
            $trend[count($trend) - 2]['final_average'] ?? 0
        );

        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            2
        );

    }



}
