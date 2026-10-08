<?php

use App\helpers\ReportViewHelpers;
use App\helpers\InsightHelpers;
use App\helpers\AnalyticsHelpers;

//Current period
$period = $data['period'] ?? [];
$isActive = (bool) ($period['is_active'] ?? false);
$isClosed = (bool) ($period['is_closed'] ?? false);


//Executive Summary
$funnel = $data['funnel'] ?? [];
$trendValues = $data['mean_score_trend'] ?? [];
$totalPeriods = count($trendValues);

$currentPeriod = $totalPeriods > 0 ? $trendValues[$totalPeriods - 1] : [];

$previousPeriod = $totalPeriods > 1 ? $trendValues[$totalPeriods - 2] : [];

$currentAverage = (float) (
    $currentPeriod['final_average']
    ?? 0
);

$previousAverage = (float) (
    $previousPeriod['final_average']
    ?? 0
);


$hasComparison   = $previousAverage > 0;
$ratingText = AnalyticsHelpers::adjectiveRating($currentAverage);

$totalEnrolled = $funnel['total_enrolled'] ?? 0;
$totalCompleted = $funnel['total_completed'] ?? 0;
$totalIncomplete = $funnel['total_incomplete'] ?? 0;
$totalUnresponsive = $funnel['total_unresponsive'] ?? 0;

$completionRate = $totalEnrolled > 0
    ? round(($totalCompleted / $totalEnrolled) * 100)
    : 0;

$trendPhrase = InsightHelpers::trendWording(
    $hasComparison,
    $currentAverage,
    $previousAverage
);


//Teacher Ranking
$teacher_ranking = $data['teacher_ranking'] ?? [];

$teacherPerformanceSummarize = ReportViewHelpers::summarizeTeacherPerformace($teacher_ranking);

//year participation
$year_participation = $data['year_participation'] ?? [];

//category performance
$category_performance = $data['category_performance'] ?? [];

//question breakdown
$question_breakdown = $data['question_breakdown'] ?? [];


?>

<?php if (!$isActive && $isClosed): ?>
<table class="insight-box-table">
    <tr>
        <td class="insight-box-cell">

            <div class="insight-title">
                Executive Evaluation Insights
            </div>

            <div class="insight-subtitle">
                Summary of institutional evaluation performance and participation
            </div>

            <!-- Summary Metrics -->
            <table class="insight-metrics">
                <tr>
                    <td class="metric-card">
                        <div class="metric-label">Overall Mean</div>
                        <div class="metric-value">
                            <?= ReportViewHelpers::number($currentAverage) ?>
                        </div>
                        <div class="metric-description">
                            <?= ReportViewHelpers::e($ratingText) ?>
                        </div>
                    </td>

                    <td class="metric-gap"></td>

                    <td class="metric-card">
                        <div class="metric-label">Participation</div>
                        <div class="metric-value">
                            <?= (int) $completionRate ?>%
                        </div>
                        <div class="metric-description">
                            <?= (int) $totalCompleted ?> of <?= (int) $totalEnrolled ?> students
                        </div>
                    </td>

                    <td class="metric-gap"></td>

                    <td class="metric-card">
                        <div class="metric-label">Teachers Evaluated</div>
                        <div class="metric-value">
                            <?= (int) ($teacherPerformanceSummarize['total_evaluated_teachers'] ?? 0) ?>
                        </div>
                        <div class="metric-description">
                            Faculty members
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Overall Performance -->
            <div class="insight-section">
                <div class="section-label">
                    Overall Performance
                </div>
                <p class="insight-body">
                    The faculty achieved an overall institutional mean score of
                    <strong><?= ReportViewHelpers::number($currentAverage) ?></strong>
                    (<em><?= ReportViewHelpers::e($ratingText) ?></em>)
                    for this evaluation cycle, <?= $trendPhrase ?>.
                </p>
            </div>

            <!-- Evaluation Participation -->
            <div class="insight-section">
                <div class="section-label">
                    Evaluation Participation
                </div>
                <p class="insight-body">
                    Student participation recorded a completion rate of
                    <strong><?= (int) $completionRate ?>%</strong>, with
                    <strong><?= (int) $totalCompleted ?></strong> of
                    <strong><?= (int) $totalEnrolled ?></strong>
                    enrolled students fully submitting their evaluations.
                    <?php if (!empty($year_participation)): ?>
                        <?= ' ' . InsightHelpers::yearParticipationInsights($year_participation); ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Faculty Performance -->
            <?php if (!empty($teacherPerformanceSummarize)): ?>
                <div class="insight-section">
                    <div class="section-label">
                        Faculty Performance
                    </div>
                    <p class="insight-body">
                        <?= InsightHelpers::teacherPerformanceInsights($teacherPerformanceSummarize); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Category Performance -->
            <?php if (!empty($category_performance)): ?>
                <div class="insight-section">
                    <div class="section-label">
                        Category Performance
                    </div>
                    <p class="insight-body">
                        <?= InsightHelpers::categoryPerformanceInsights($category_performance); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Question-Level Performance -->
            <?php if (!empty($question_breakdown)): ?>
                <div class="insight-section">
                    <div class="section-label">
                        Question-Level Performance
                    </div>
                    <p class="insight-body">
                        <?= InsightHelpers::questionPerformanceInsights($question_breakdown); ?>
                    </p>
                </div>
            <?php endif; ?>

        </td>
    </tr>
</table>
<?php endif; ?>