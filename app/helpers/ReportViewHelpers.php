<?php

namespace App\helpers;

use App\helpers\AnalyticsHelpers;

final class ReportViewHelpers
{
    public static function ratingColor(
        string $rating
    ): array {

        return match($rating) {

            'Outstanding' => [
                      'bg' => '#EAF3DE',
                      'text' => '#639922',
                      'border' => '#97C459',
                  ],

            'Very Satisfactory' => [
                'bg' => '#E1F5EE',
                'text' => '#1D9E75',
                'border' => '#5DCAA5',
            ],

            'Satisfactory' => [
                'bg' => '#E6F1FB',
                'text' => '#378ADD',
                'border' => '#85B7EB',
            ],

            'Fair' => [
                'bg' => '#FAEEDA',
                'text' => '#BA7517',
                'border' => '#EF9F27',
            ],

            'Poor' => [
                'bg' => '#FCEBEB',
                'text' => '#E24B4A',
                'border' => '#F09595',
            ],

            default => [
                'bg' => '#F1EFE8',
                'text' => '#444441',
                'border' => '#B4B2A9',
            ],

        };

    }


    public static function rankStyle(
        int $rank
    ): array {

        return match ($rank) {
            1 => [
                'bg' => '#FAEEDA',
                'text' => '#633806',
                'border' => '#EF9F27',
            ],

            2 => [
                'bg' => '#F1EFE8',
                'text' => '#444441',
                'border' => '#B4B2A9',
            ],

            3 => [
                'bg' => '#FAECE7',
                'text' => '#712B13',
                'border' => '#F0997B',
            ],

            default => [
                'bg' => '#EEEDFE',
                'text' => '#3C3489',
                'border' => '#AFA9EC',
            ],
        };

    }

    public static function scoreBar(
        float $score,
        float $max = 5.0,
        int $width = 64
    ): string {
        if ($max <= 0) {
            return '';
        }

        $percentage = ($score / $max) * 100;
        $percentage = max(0, min(100, $percentage));

        $percentage = round($percentage);

        $color = match (true) {
            $score >= 4.5 => '#639922',
            $score >= 3.5 => '#1D9E75',
            $score >= 2.5 => '#378ADD',
            $score >= 1.5 => '#BA7517',
            default => '#E24B4A',
        };

        return sprintf(
            '
            <div style="display:inline-block;vertical-align:middle;margin-left:6px;">
                <div style="
                    width:%dpx;
                    height:5px;
                    background:#EEECE7;
                    border-radius:3px;
                    overflow:hidden;
                ">
                    <div style="
                        width:%d%%;
                        height:100%%;
                        background:%s;
                        border-radius:3px;
                    "></div>
                </div>

                <span style="
                    font-size:9px;
                    color:#9E9A93;
                    margin-left:2px;
                ">%d%%</span>
            </div>
            ',
            $width,
            $percentage,
            $color,
            $percentage
        );
    }

    public static function sectionAccent(): string
    {
        return '
            <div style="
                width:3px;
                height:14px;
                background:#534AB7;
                border-radius:2px;
                display:inline-block;
                vertical-align:middle;
                margin-right:7px;
            "></div>
        ';
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    public static function number(
        mixed $value,
        int $decimals = 2
    ): string {
        return number_format(
            (float) ($value ?? 0),
            $decimals
        );
    }


    public static function summarizeTeacherPerformace(
        array $teacher_ranking
    ): array {

        $ratingCounts = [
          'Outstanding' => 0,
          'Very Satisfactory' => 0,
          'Satisfactory' => 0,
          'Fair' => 0,
          'Poor' => 0
        ];

        $total_teachers_evaluated = count($teacher_ranking);
        $totalScore = 0;
        $highestScore = null;
        $lowestScore = null;
        $allScore = [];


        foreach ($teacher_ranking as $teacher) {

            $score  = (float) $teacher['mean_score'];

            $rating = AnalyticsHelpers::adjectiveRating($score);


            if (isset($ratingCounts[$rating])) {
                $ratingCounts[$rating]++;
            }

            $totalScore += $score;

            if ($highestScore === null || $score > $highestScore) {
                $highestScore = $score;
            }

            if ($lowestScore === null || $score < $lowestScore) {
                $lowestScore = $score;
            }

        }

        $averageScore = $totalScore > 0
          ? $totalScore / $total_teachers_evaluated
          : 0;

        $scoreDiff = ($highestScore !== 0 && $lowestScore !== 0)
          ? $highestScore - $lowestScore
          : 0;

        return [
          'total_evaluated_teachers' => $total_teachers_evaluated,
          'average_score' => $averageScore,
          'score_diff' => $scoreDiff,
          'highest_score' => $highestScore,
          'lowest_score' => $lowestScore,
          'rating_counts' => $ratingCounts
        ];


    }

}
