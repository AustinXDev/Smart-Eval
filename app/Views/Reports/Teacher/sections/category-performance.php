
<?php

use App\helpers\ReportViewHelpers;
use App\helpers\AnalyticsHelpers;

$info = is_array($data['info'] ?? null) ? $data['info'] : [];
$categories = is_array($data['categories'] ?? null) ? $data['categories'] : [];
$overallScore = (float) ($info['average_score'] ?? 0);

?>

<div class="category-breakdown-container">

  <div class="section-title">
    <span></span> Category Performance
  </div>

  <?php if ($categories !== []): ?>
    <table class="category-breakdown-table">
      <thead>
        <tr>
          <th class="category-column">Category</th>
          <th class="category-score-column">Average Score</th>
          <th class="category-visual-column">Visual</th>
          <th class="category-delta-column">VS Overall</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $category): ?>
          <?php
          $categoryScore = (float) ($category['average_score'] ?? 0);
            $gaugePercent = max(0, min(100, ($categoryScore / 5) * 100));
            $categoryRating = AnalyticsHelpers::adjectiveRating($categoryScore);
            $categoryColors = ReportViewHelpers::ratingColor($categoryRating);
            $difference =  $categoryScore - $overallScore;
            $differenceClass = $difference > 0
                ? 'delta-positive'
                : ($difference < 0 ? 'delta-negative' : 'delta-neutral');
            ?>
          <tr>
            <td class="category-name">
              <?= ReportViewHelpers::e($category['category'] ?? 'Uncategorized') ?>
            </td>
            <td class="category-score-column">
              <?= ReportViewHelpers::number($categoryScore) ?> / 5.00
            </td>
            <td>
              <div class="category-gauge">
                <div class="category-gauge-fill" style="
                  width: <?= $gaugePercent ?>%;
                  background-color: <?= ReportViewHelpers::e($categoryColors['text']) ?>;
                "></div>
              </div>
              <span class="category-gauge-percent">
                <?= ReportViewHelpers::number($gaugePercent, 0) ?>%
              </span>
            </td>
            <td class="category-delta-column <?= $differenceClass ?>">
              <?= sprintf('%+.2f', $difference) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="category-empty-message">
      No category results are available for this evaluation.
    </p>
  <?php endif; ?>

</div>