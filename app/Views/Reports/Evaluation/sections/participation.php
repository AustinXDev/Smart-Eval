<?php

use App\helpers\ReportTheme;
use App\helpers\ReportViewHelpers;

$funnel = $data['funnel'] ?? [];

$totalEnrolled = (int) (
    $funnel['total_enrolled'] ?? 0
);

$totalCompleted = (int) (
    $funnel['total_completed'] ?? 0
);

$totalIncomplete = (int) (
    $funnel['total_incomplete'] ?? 0
);

$totalUnresponsive = (int) (
    $funnel['total_unresponsive'] ?? 0
);

$completionRate = $totalEnrolled > 0
    ? round(($totalCompleted / $totalEnrolled) * 100)
    : 0;

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

$growth          = 0;
$growthPercent   = 0;
$isPositive      = true;

if ($previousAverage > 0) {

    $growth = $currentAverage - $previousAverage;
    $growthPercent = ($growth / $previousAverage) * 100;
    $isPositive = $growth >= 0;

}

$formattedGrowth = ($isPositive ? '+' : '') . ReportViewHelpers::number($growth);

?>

<span class="section-title">
    <?= ReportViewHelpers::sectionAccent() ?>
    Executive Summary
</span>

<table class="stat-grid">
    <tr>
        <td class="stat-card neutral">
            <p class="stat-label">Total Students</p>
            <p 
            class="stat-value"
            style="
              color:#27500A; 
              font-size: 18px; 
              font-weight: bold;
              margin: 8px;
            "
            >
              <?= $totalEnrolled ?>
            </p>
            <p class="stat-sub" style="color:#639922;">Total Enrolled</p>
        </td>
        <td class="stat-card neutral">
            <p class="stat-label">Completed</p>

            <p 
            class="stat-value" 
            style="
              color:#27500A; 
              font-size: 18px; 
              font-weight: bold;
              margin: 8px;
            ">
              <?= $totalCompleted ?>
            </p>

            <p class="stat-sub" style="color:#639922;">fully submitted</p>
        </td>
        <td class="stat-card neutral">
            <p class="stat-label">Incomplete</p>
            <p 
              class="stat-value" 
              style="
                color:#BA7517;
                font-size: 18px; 
                font-weight: bold;
                margin: 8px;
              ">
              <?= $totalIncomplete ?>
            </p>
            <p class="stat-sub" style="color:#EF9F27;">partially completed</p>
        </td>
    </tr>
    <tr>
        <td class="stat-card neutral">
            <p class="stat-label">Unresponsive</p>
            <p 
              class="stat-value" 
              style="
                color:#791F1F;
                font-size: 18px; 
                font-weight: bold;
                margin: 8px;
              ">
              <?= $totalUnresponsive ?>
            </p>
            <p class="stat-sub" style="color:#E24B4A;">no submission</p>
        </td>
        <td class="stat-card neutral">
            <p class="stat-label">Completion Rate</p>
            <p 
              class="stat-value" 
              style="
                color:#2D1B69;
                font-size: 18px; 
                font-weight: bold;
                margin: 8px;
            ">
              <?= $completionRate ?>%
            </p>
            <p class="stat-sub" style="color:#534AB7;">evaluation completion</p>
        </td>
        <td class="stat-card hero-card">
            <div class="stat-title">Overall Mean Score</div>
            <div class="stat-number score-value"><?= $currentAverage ?></div>

            <?php if ($previousAverage > 0): ?>

              <div class="growth-badge <?= $isPositive ? 'positive' : 'negative' ?>">
                  <span class="growth-arrow"><?= $isPositive ? '▲' : '▼' ?></span>
                  <?= $formattedGrowth ?> vs prev. period
              </div>
            
            <?php else: ?>


            <?php endif ?>
            <div class="stat-sub">out of 5.00</div>
        </td>
    </tr>
</table>