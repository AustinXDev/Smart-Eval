<?php

use App\helpers\ReportViewHelpers;

$insights = is_array($data['insights'] ?? null) ? $data['insights'] : [];

?>

<div class="teacher-insights-container">

  <div class="section-title">
    <span></span> Evaluation Insights
  </div>

  <?php if (!empty($insights['overall'])): ?>
    <div class="teacher-insight-overall">
      <div class="teacher-insight-label">Overall Performance</div>
      <p><?= ReportViewHelpers::e($insights['overall']) ?></p>
    </div>
  <?php endif; ?>

  <?php foreach ([
      'strengths' => 'Strengths',
      'improvements' => 'Areas for Improvement',
      'patterns' => 'Performance Patterns',
  ] as $key => $label): ?>
    <?php if (!empty($insights[$key]) && is_array($insights[$key])): ?>
      <div class="teacher-insight-group">
        <div class="teacher-insight-label"><?= ReportViewHelpers::e($label) ?></div>
        <?php foreach ($insights[$key] as $finding): ?>
          <p><?= ReportViewHelpers::e($finding) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if (!empty($insights['neutral'])): ?>
    <p class="teacher-insight-neutral">
      <?= ReportViewHelpers::e($insights['neutral']) ?>
    </p>
  <?php endif; ?>

</div>
