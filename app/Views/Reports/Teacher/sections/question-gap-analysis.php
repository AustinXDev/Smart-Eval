<?php

use App\helpers\ReportViewHelpers;

$info = is_array($data['info'] ?? null) ? $data['info'] : [];
$questionData = is_array($data['questions'] ?? null) ? $data['questions'] : [];
$overallScore = (float) ($info['average_score'] ?? 0);
$strengths = is_array($questionData['strengths'] ?? null)
    ? $questionData['strengths']
    : (is_array($questionData['strenghts'] ?? null) ? $questionData['strenghts'] : []);
$improvements = is_array($questionData['areas_for_improvement'] ?? null)
    ? $questionData['areas_for_improvement']
    : [];

?>

<div class="question-gap-analysis-container">

  <div class="section-title">
    <span></span> Question Gap Analysis
  </div>

  <table class="question-gap-columns">
    <tbody>
      <tr>
        <td class="question-gap-panel strength-panel">
          <div class="question-gap-panel-title">Strongest Questions</div>

          <?php if ($strengths !== []): ?>
            <table class="question-gap-table">
              <thead>
                <tr>
                  <th class="gap-question-column">Question</th>
                  <th class="gap-score-column">Score</th>
                  <th class="gap-delta-column">Gap</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($strengths as $question): ?>
                  <?php
                  $questionScore = (float) ($question['average_score'] ?? 0);
                  $gap = $questionScore - $overallScore;
                  $gapClass = $gap > 0
                      ? 'gap-positive'
                      : ($gap < 0 ? 'gap-negative' : 'gap-neutral');
                  ?>
                  <tr>
                    <td class="gap-question">
                      <?= ReportViewHelpers::e($question['question_text'] ?? '') ?>
                    </td>
                    <td class="gap-score">
                      <?= ReportViewHelpers::number($questionScore) ?>
                    </td>
                    <td class="gap-delta <?= $gapClass ?>">
                      <?= sprintf('%+.2f', $gap) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="question-gap-empty">No strongest questions are available.</p>
          <?php endif; ?>
        </td>

        <td class="question-gap-spacer"></td>

        <td class="question-gap-panel improvement-panel">
          <div class="question-gap-panel-title">Needs Improvement</div>

          <?php if ($improvements !== []): ?>
            <table class="question-gap-table">
              <thead>
                <tr>
                  <th class="gap-question-column">Question</th>
                  <th class="gap-score-column">Score</th>
                  <th class="gap-delta-column">Gap</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($improvements as $question): ?>
                  <?php
                  $questionScore = (float) ($question['average_score'] ?? 0);
                  $gap = $questionScore - $overallScore;
                  $gapClass = $gap > 0
                      ? 'gap-positive'
                      : ($gap < 0 ? 'gap-negative' : 'gap-neutral');
                  ?>
                  <tr>
                    <td class="gap-question">
                      <?= ReportViewHelpers::e($question['question_text'] ?? '') ?>
                    </td>
                    <td class="gap-score">
                      <?= ReportViewHelpers::number($questionScore) ?>
                    </td>
                    <td class="gap-delta <?= $gapClass ?>">
                      <?= sprintf('%+.2f', $gap) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="question-gap-empty">No areas for improvement were identified.</p>
          <?php endif; ?>
        </td>
      </tr>
    </tbody>
  </table>

</div>