<?php

use App\helpers\ReportTheme;
use App\helpers\ReportViewHelpers;

$questionBreakdown = $data['question_breakdown'] ?? [];

$strengths = $questionBreakdown['strengths'] ?? [];

$weaknesses = $questionBreakdown['weaknesses'] ?? [];

?>

<div class="keep-together">

  <span class="section-title">
      <?= ReportViewHelpers::sectionAccent() ?>
      Question Performance
  </span>

  <!-- Outer 2-Column Table for Dompdf Layout -->
  <table class="perf-wrapper-table">
      <tr>
          <!-- Strengths Column -->
          <td class="perf-col-cell">
              <div class="col-label high">
                  ▲ &nbsp; Top Rated Questions
              </div>
              <table class="perf-table">
                  <thead>
                      <tr>
                          <th class="col-question">Question</th>
                          <th class="col-score center">Avg.</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($strengths)): ?>
                          <tr>
                              <td colspan="2" class="no-data center">No top questions identified.</td>
                          </tr>
                      <?php else: ?>
                          <?php foreach ($strengths as $question): ?>
                              <tr>
                                  <td class="text-left">
                                      <?= ReportViewHelpers::e($question['question'] ?? '') ?>
                                  </td>
                                  <td class="center score-high">
                                      <?= ReportViewHelpers::number($question['score'] ?? 0) ?>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </td>

          <!-- Spacer Column -->
          <td class="perf-col-gap"></td>

          <!-- Weaknesses Column -->
          <td class="perf-col-cell">
              <div class="col-label low">
                  ▼ &nbsp; Needs Improvement
              </div>
              <table class="perf-table">
                  <thead>
                      <tr>
                          <th class="col-question">Question</th>
                          <th class="col-score center">Avg.</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($weaknesses)): ?>
                          <tr>
                              <td colspan="2" class="no-data center">No improvement areas identified.</td>
                          </tr>
                      <?php else: ?>
                          <?php foreach ($weaknesses as $question): ?>
                              <tr>
                                  <td class="text-left">
                                      <?= ReportViewHelpers::e($question['question'] ?? '') ?>
                                  </td>
                                  <td class="center score-low">
                                      <?= ReportViewHelpers::number($question['score'] ?? 0) ?>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </td>
      </tr>
  </table>
  
</div>