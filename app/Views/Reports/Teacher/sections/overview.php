
<?php

use App\helpers\ReportViewHelpers;
use App\helpers\AnalyticsHelpers;

$info = is_array($data['info'] ?? null) ? $data['info'] : [];
$score = (float) ($info['average_score'] ?? 0);
$totalEvaluated = (int) ($info['total_evaluated'] ?? $meta['valid_student_count'] ?? 0);
$scorePercent = max(0, min(100, ($score / 5) * 100));
$rating = AnalyticsHelpers::adjectiveRating($score);

$colors = ReportViewHelpers::ratingColor($rating);
?>

<div class="overview-container">

  <div class="section-title">
    <span class="title"></span> Overall Performance
  </div>

  <div class="overview">

    <div class="overall-score-container">
      
      <div class="overall-score"><?= ReportViewHelpers::number($score) ?></div>
      <div class="overall-score-sub">out of 5.00</div>
      <div class="adjective-rating" style="
        background-color: <?= $colors['bg'] ?>;
        color: <?= $colors['text'] ?>;
        border: 1px solid <?= $colors['border'] ?>;
      ">
        <?= $rating ?>
      </div>

    </div>

    <div class="overview-cards">

      <div class="overview-cards-2">

        <div class="total-evaluated-container">

          <div class="total-evaluated-title">
            Total Evaluators
          </div>

          <div class="total-count">
            <?= $totalEvaluated ?>
          </div>

          <div class="total-sub-text">
            <?= $totalEvaluated === 1 ? 'student responded' : 'students responded' ?>
          </div>

        </div>

        <div class="score-range-container">

          <div class="score-range-title">
            Score Range
          </div>

          <div class="range">
            1.00 - 5.00
          </div>

          <div class="score-range-subtext">
            Likert scale
          </div>

        </div>

      </div>

      <div class="score-visual-container">

        <div class="score-visual-title">
          Score Visual
        </div>

        <div class="gauge">
          <div class="line" style="
            width: <?= $scorePercent ?>%;
            background-color: <?= $colors['text'] ?>;
          "></div>
        </div>

        <div class="mean-level">
          <span class="low">1.00</span>
          <span class="mid" style="font-weight: bold;">
            <?= ReportViewHelpers::number($score) ?>
          </span>
          <span class="high">5.00</span>
        </div>

      </div>

    </div>

  </div>

</div>