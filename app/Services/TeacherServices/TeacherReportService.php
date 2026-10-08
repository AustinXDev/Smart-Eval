<?php

namespace App\Services\TeacherServices;

use App\Repositories\ReportRepositories\TeacherReportRepository;
use App\helpers\AnalyticsHelpers;
use RuntimeException;

class TeacherReportService
{
    public function __construct(
        private TeacherReportRepository $teacherReportRepo,
        private AnalyticsHelpers $helpers
    ) {
    }

    /**
     * Build the complete individual teacher report.
     */

    public function getIndividualTeacherReport(
        int $periodId,
        int $teacherId,
    ): ?array {

        # Find evaluation period if exist
        $period = $this->teacherReportRepo->getPeriodById($periodId);

        if ($period === null) {
            throw new RuntimeException(
                'Evaluation period not found.'
            );
        }


        # Determine students whose required evaluations for the period are complete.

        $validStudentIds = $this->teacherReportRepo->getValidStudentIds($periodId);

        if (empty($validStudentIds)) {
            throw new RuntimeException(
                "No valid students found for period ID: {$periodId}"
            );
        }


        # Get teachers overall performance.

        $summary = $this->teacherReportRepo->getTeacherSummary(
            $periodId,
            $teacherId,
            $validStudentIds
        );

        if (!$summary) {
            throw new RuntimeException(
                "No teacher summary found. Period ID: {$periodId}, Teacher ID: {$teacherId}"
            );
        }


        # Convert database values to proper type

        $summary['average_score'] = (float) $summary['average_score'];

        $summary['total_evaluated'] = (int) $summary['total_evaluated'];

        $summary['adjective_rating'] = $this->helpers->adjectiveRating($summary['average_score']);


        # Category Performance.

        $categories = $this->teacherReportRepo->getCategoryBreakdown($periodId, $teacherId, $validStudentIds);


        # Question-level performance.

        $questions = $this->teacherReportRepo->getQuestionPerformance($periodId, $teacherId, $validStudentIds);


        $strengths = [];
        $areasForImprovement = [];

        foreach ($questions as $question) {

            $score = (float) $question['average_score'];

            if ($score < 3.0) {

                $areasForImprovement[] = $question;

            } else {

                $strengths[] = $question;

            }

        }

        $insights = $this->buildIndividualInsights(
            $summary,
            $categories,
            $questions
        );

        return [
          'info' => $summary,

          'categories' => $categories,

          'questions' => [
              'all' => $questions,
              'areas_for_improvement' => $areasForImprovement,
              'strenghts' => $strengths
          ],

          'insights' => $insights,

          'meta' => [
            'period' => $period['academic_year'],
            'teacher' => $summary['full_name'],
            'valid_student_count' => count($validStudentIds)
          ]
        ];

    }

    /**
     * Prepare evidence-based individual findings for the PDF view.
     */
    private function buildIndividualInsights(
        array $summary,
        array $categories,
        array $questions
    ): array {

        $overallScore = (float) ($summary['average_score'] ?? 0);
        $rating = $this->helpers->adjectiveRating($overallScore);
        $ratingBand = match ($rating) {
            'Outstanding' => 'the highest rating band',
            'Very Satisfactory' => 'the upper rating band',
            'Satisfactory' => 'the middle rating band',
            'Fair' => 'a lower rating band',
            default => 'the lowest rating band',
        };
        $evaluatedCount = (int) ($summary['total_evaluated'] ?? 0);

        $overallText = sprintf(
            'The evaluation results indicate an overall mean of %.2f out of 5.00 (%s), placing the result in %s of the established scale%s.',
            $overallScore,
            $rating,
            $ratingBand,
            $evaluatedCount > 0
                ? sprintf(' across %d completed student evaluation%s', $evaluatedCount, $evaluatedCount === 1 ? '' : 's')
                : ''
        );

        $categoryResults = $this->normalizeInsightResults(
            $categories,
            'category'
        );
        $questionResults = $this->normalizeInsightResults(
            $questions,
            'question_text'
        );

        $strengths = [];
        $improvements = [];
        $patterns = [];

        $highestCategory = $this->findExtremeResult($categoryResults, true);
        if (
            $highestCategory !== null
            && $highestCategory['score'] >= 3.5
        ) {
            $strengths[] = $this->describeRelativeResult(
                'The highest-rated category was',
                $highestCategory,
                $overallScore
            );
        }

        $highestQuestion = $this->findExtremeResult($questionResults, true);
        if (
            $highestQuestion !== null
            && $highestQuestion['score'] >= 3.5
        ) {
            $strengths[] = $this->describeRelativeResult(
                'The highest-rated question was',
                $highestQuestion,
                $overallScore
            );
        }

        $lowestCategory = $this->findExtremeResult($categoryResults, false);
        if (
            $lowestCategory !== null
            && $this->warrantsReview($lowestCategory['score'], $overallScore)
        ) {
            $improvements[] = $this->describeLowerResult(
                'The lowest-rated category was',
                $lowestCategory,
                $overallScore
            );
        }

        $lowestQuestion = $this->findExtremeResult($questionResults, false);
        if (
            $lowestQuestion !== null
            && $this->warrantsReview($lowestQuestion['score'], $overallScore)
        ) {
            $improvements[] = $this->describeLowerResult(
                'The lowest-rated question was',
                $lowestQuestion,
                $overallScore
            );
        }

        $categoryPattern = $this->describeScorePattern(
            $categoryResults,
            'category'
        );
        if ($categoryPattern !== null) {
            $patterns[] = $categoryPattern;
        }

        $questionPattern = $this->describeScorePattern(
            $questionResults,
            'question'
        );
        if ($questionPattern !== null) {
            $patterns[] = $questionPattern;
        }

        $neutral = null;

        if ($categoryResults === [] && $questionResults === []) {
            $neutral = 'Category and question comparisons are not available for this evaluation; no further comparison is reported.';
        } elseif ($strengths === [] && $improvements === [] && $patterns === []) {
            $neutral = 'The available category and question scores do not show a notable finding under the report comparison criteria.';
        }

        return [
            'overall' => $overallText,
            'strengths' => $strengths,
            'improvements' => $improvements,
            'patterns' => $patterns,
            'neutral' => $neutral,
        ];
    }

    private function normalizeInsightResults(
        array $rows,
        string $labelKey
    ): array {

        $results = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['average_score']) || !is_numeric($row['average_score'])) {
                continue;
            }

            $label = trim((string) ($row[$labelKey] ?? ''));
            if ($label === '') {
                continue;
            }

            $results[] = [
                'label' => $label,
                'score' => (float) $row['average_score'],
            ];
        }

        usort(
            $results,
            static fn (array $a, array $b): int => $a['score'] <=> $b['score']
        );

        return $results;
    }

    private function findExtremeResult(
        array $results,
        bool $highest
    ): ?array {

        if ($results === []) {
            return null;
        }

        return $highest
            ? $results[array_key_last($results)]
            : $results[0];
    }

    private function describeRelativeResult(
        string $prefix,
        array $result,
        float $overallScore
    ): string {

        $difference = $result['score'] - $overallScore;
        $comparison = abs($difference) < 0.005
            ? 'matching the overall mean'
            : sprintf(
                '%s the overall mean by %.2f points',
                $difference > 0 ? 'exceeding' : 'falling below',
                abs($difference)
            );

        return sprintf(
            '%s %s, with a mean of %.2f out of 5.00, %s. This result is among the strongest measured results in its comparison group.',
            $prefix,
            $result['label'],
            $result['score'],
            $comparison
        );
    }

    private function warrantsReview(
        float $score,
        float $overallScore
    ): bool {

        return $score < 2.5 || ($overallScore - $score) >= 0.5;
    }

    private function describeLowerResult(
        string $prefix,
        array $result,
        float $overallScore
    ): string {

        $difference = $overallScore - $result['score'];
        $rating = $this->helpers->adjectiveRating($result['score']);
        $relativePhrase = $difference >= 0.5
            ? sprintf('%.2f points below the overall mean', $difference)
            : 'within the lower rating bands of the five-point scale';

        return sprintf(
            '%s %s, with a mean of %.2f out of 5.00 (%s), %s. This comparatively lower result may benefit from review.',
            $prefix,
            $result['label'],
            $result['score'],
            $rating,
            $relativePhrase
        );
    }

    private function describeScorePattern(
        array $results,
        string $comparisonGroup
    ): ?string {

        if (count($results) < 2) {
            return null;
        }

        $lowest = $results[0];
        $highest = $results[array_key_last($results)];
        $spread = $highest['score'] - $lowest['score'];

        if ($spread < 0.5) {
            return sprintf(
                'The measured %s results were closely grouped, spanning %.2f points; no notable difference is evident within this comparison.',
                $comparisonGroup === 'category' ? 'category' : 'question',
                $spread
            );
        }

        return sprintf(
            'A %.2f-point spread separates the highest and lowest %s results (%s: %.2f; %s: %.2f), indicating a notable difference within this comparison.',
            $spread,
            $comparisonGroup,
            $highest['label'],
            $highest['score'],
            $lowest['label'],
            $lowest['score']
        );
    }

}
