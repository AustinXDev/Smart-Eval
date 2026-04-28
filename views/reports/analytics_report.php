<?php
$imagePath = __DIR__ . '/../../public/assets/images/aite-logo.png';
$base64    = base64_encode(file_get_contents($imagePath));
$logo      = 'data:image/png;base64,' . $base64;

// ── Purple theme tokens ───────────────────────────────────
define('CLR_PURPLE_DARK',   '#2D1B69');
define('CLR_PURPLE',        '#3C3489');
define('CLR_PURPLE_MID',    '#534AB7');
define('CLR_PURPLE_LIGHT',  '#EEEDFE');
define('CLR_PURPLE_BORDER', '#AFA9EC');

// ── Helpers ──────────────────────────────────────────────
function ratingColor(string $rating): array {
    return match($rating) {
        'Outstanding'       => ['bg' => '#EAF3DE', 'text' => '#27500A', 'border' => '#97C459'],
        'Very Satisfactory' => ['bg' => '#E1F5EE', 'text' => '#085041', 'border' => '#5DCAA5'],
        'Satisfactory'      => ['bg' => '#E6F1FB', 'text' => '#0C447C', 'border' => '#85B7EB'],
        'Fair'              => ['bg' => '#FAEEDA', 'text' => '#633806', 'border' => '#EF9F27'],
        'Poor'              => ['bg' => '#FCEBEB', 'text' => '#791F1F', 'border' => '#F09595'],
        default             => ['bg' => '#F1EFE8', 'text' => '#444441', 'border' => '#B4B2A9'],
    };
}

function rankStyle(int $n): array {
    return match($n) {
        1 => ['bg' => '#FAEEDA', 'text' => '#633806', 'border' => '#EF9F27'],
        2 => ['bg' => '#F1EFE8', 'text' => '#444441', 'border' => '#B4B2A9'],
        3 => ['bg' => '#FAECE7', 'text' => '#712B13', 'border' => '#F0997B'],
        default => ['bg' => CLR_PURPLE_LIGHT, 'text' => CLR_PURPLE, 'border' => CLR_PURPLE_BORDER],
    };
}

function scoreBar(float $score, float $max = 5.0, int $width = 64): string {
    $pct   = round(($score / $max) * 100);
    $color = $score >= 4.5 ? '#639922'
           : ($score >= 3.5 ? '#1D9E75'
           : ($score >= 2.5 ? '#378ADD'
           : ($score >= 1.5 ? '#BA7517' : '#E24B4A')));
    return "
        <div style='display:inline-block;vertical-align:middle;margin-left:6px;'>
          <div style='width:{$width}px;height:5px;background:#EEECE7;border-radius:3px;overflow:hidden;'>
            <div style='width:{$pct}%;height:100%;background:{$color};border-radius:3px;'></div>
          </div>
          <span style='font-size:9px;color:#9E9A93;margin-left:2px;'>{$pct}%</span>
        </div>
    ";
}

function sectionAccent(): string {
    return "<div style='width:3px;height:14px;background:" . CLR_PURPLE_MID . ";border-radius:2px;display:inline-block;vertical-align:middle;margin-right:7px;'></div>";
}
 
$trendData = $data['trend']['trend'] ?? [];
$currentPeriod = end($trendData);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    color: #1A1714;
    background: #fff;
  }

  /* ── Cover band ── */
  .cover-band {
    background: <?= CLR_PURPLE_DARK ?>;
    padding: 28px 36px 22px;
    margin-bottom: 0;
  }
  .cover-band .school-name {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 2px;
  }
  .cover-band .school-addr {
    font-size: 10px;
    color: <?= CLR_PURPLE_BORDER ?>;
    margin-bottom: 14px;
  }
  .cover-band .report-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
    letter-spacing: -0.3px;
  }
  .cover-band .report-sub {
    font-size: 11px;
    color: <?= CLR_PURPLE_BORDER ?>;
  }
  .cover-logo {
    float: right;
    margin-top: -4px;
  }
  .cover-clearfix { clear: both; }

  /* ── Purple rule under cover ── */
  .cover-rule {
    height: 4px;
    background: linear-gradient(90deg, <?= CLR_PURPLE_MID ?>, <?= CLR_PURPLE_BORDER ?>, #fff);
  }

  /* ── Body padding ── */
  .body-wrap { padding: 24px 36px 32px; }

  /* ── Section titles ── */
  .section-title {
    font-size: 10px;
    font-weight: 600;
    color: <?= CLR_PURPLE ?>;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 10px;
    margin-top: 22px;
    padding-bottom: 6px;
    border-bottom: 0.5px solid <?= CLR_PURPLE_BORDER ?>;
    display: block;
  }

  /* ── Stat cards ── */
  .stat-row  { width:100%; display:table; border-spacing:7px; margin-bottom:4px; }
  .stat-card {
    display: table-cell;
    background: <?= CLR_PURPLE_LIGHT ?>;
    border: 0.5px solid <?= CLR_PURPLE_BORDER ?>;
    border-top: 2.5px solid <?= CLR_PURPLE_MID ?>;
    border-radius: 7px;
    padding: 10px 12px;
    text-align: left;
    vertical-align: top;
  }
  .stat-card.neutral {
    background: #F9F8F5;
    border-top-color: #DEDAD4;
    border-color: #DEDAD4;
  }
  .stat-label {
    font-size: 9px;
    color: <?= CLR_PURPLE ?>;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 5px;
  }
  .stat-card.neutral .stat-label { color: #6B6B60; }
  .stat-value { font-size: 20px; font-weight: 700; color: <?= CLR_PURPLE_DARK ?>; }
  .stat-sub   { font-size: 9px;  color: <?= CLR_PURPLE_MID ?>; margin-top: 2px; }
  .stat-card.neutral .stat-sub { color: #9E9A93; }

  /* ── Divider ── */
  .divider { height:0.5px; background:#DEDAD4; width:100%; margin:10px 0; }

  /* ── Tables ── */
  table { width:100%; border-collapse:collapse; page-break-inside:auto; }
  thead { display:table-header-group; }
  tr    { page-break-inside:avoid; }

  th {
    background: <?= CLR_PURPLE_DARK ?>;
    padding: 8px 10px;
    text-align: left;
    font-size: 9px;
    font-weight: 600;
    color: <?= CLR_PURPLE_BORDER ?>;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: none;
  }
  th.center, td.center { text-align:center; }
  th:first-child { border-radius:0; }

  td {
    padding: 8px 10px;
    font-size: 11px;
    color: #1A1714;
    border-bottom: 0.5px solid #EEECE7;
    vertical-align: middle;
  }
  tr:last-child td        { border-bottom:none; }
  tr:nth-child(even) td   { background: #FAFAF8; }

  .table-wrap {
    border: 0.5px solid <?= CLR_PURPLE_BORDER ?>;
    border-radius: 8px;
    overflow: hidden;
  }

  /* ── Rating pill ── */
  .rating-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 600;
    border-width: 0.5px;
    border-style: solid;
  }

  /* ── Rank circle ── */
  .rank-circle {
    display: inline-block;
    width: 22px;
    height: 22px;
    line-height: 22px;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 700;
    text-align: center;
    border-width: 0.5px;
    border-style: solid;
  }

  /* ── Two-col layout for questions ── */
  .two-col        { display:table; width:100%; border-spacing:10px; }
  .two-col-cell   { display:table-cell; width:50%; vertical-align:top; }
  .col-label {
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 5px 8px;
    border-radius: 4px 4px 0 0;
    margin-bottom: 0;
  }
  .col-label.high { background:#EAF3DE; color:#27500A; }
  .col-label.low  { background:#FCEBEB; color:#791F1F; }

  /* ── Footer ── */
  .footer {
    margin-top: 2.5rem;
    padding: 10px 36px;
    border-top: 2px solid <?= CLR_PURPLE_DARK ?>;
    display: table;
    width: 100%;
    background: <?= CLR_PURPLE_LIGHT ?>;
  }
  .footer-left  { display:table-cell; font-size:9px; color:<?= CLR_PURPLE ?>; font-weight:500; }
  .footer-right { display:table-cell; text-align:right; font-size:9px; color:<?= CLR_PURPLE_MID ?>; }

  @page  { size:A4; margin:0; }
  @media print { body { padding:0; } }
</style>
</head>
<body>

<!-- ── Cover Band ── -->
<div class="cover-band">
  <img class="cover-logo" src="<?= $logo ?>" width="48">
  <p class="school-name">Asian Institute of Technology and Education</p>
  <p class="school-addr">Gret-Fisico Bldg., Maharlika Highway, Lumingon, Tiaong, Quezon</p>
  <div class="cover-clearfix"></div>
  <p class="report-title">Faculty Evaluation Summary Report</p>
  <p class="report-sub">
    Academic Year <?= htmlspecialchars($data['meta']['academic_year'] ?? []) ?>
    &nbsp;&mdash;&nbsp;
    <?= htmlspecialchars($data['meta']['semester'] ?? []) ?>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    Generated <?= date('F j, Y') ?>
  </p>
</div>
<div class="cover-rule"></div>

<div class="body-wrap">

<!-- ── Overall Performance ── -->
<span class="section-title"><?= sectionAccent() ?>Overall Performance</span>
<div class="stat-row">
  <div class="stat-card">
    <p class="stat-label">Mean Score</p>
    <p class="stat-value"><?= number_format($currentPeriod['final_average'] ?? 0, 2) ?></p>
    <p class="stat-sub">out of 5.00</p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Growth Rate</p>
    <p class="stat-value"><?= htmlspecialchars($data['trend']['growth'] ?? []) ?>%</p>
    <p class="stat-sub">vs. previous semester</p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Adjective Rating</p>
    <?php $rc = ratingColor($data['trend']['adjectiveRating'] ?? []); ?>
    <p class="stat-value" style="font-size:15px;color:<?= $rc['text'] ?>;">
      <?= htmlspecialchars($data['trend']['adjectiveRating'] ?? [])  ?>
    </p>
    <p class="stat-sub">department average</p>
  </div>
</div>

<!-- ── Participation Summary ── -->
<span class="section-title"><?= sectionAccent() ?>Participation Summary</span>
<div class="stat-row">
  <div class="stat-card neutral">
    <p class="stat-label">Total Students</p>
    <p class="stat-value"><?= $data['funnel']['total'] ?? 0 ?></p>
  </div>
  <div class="stat-card neutral">
    <p class="stat-label">Completed</p>
    <p class="stat-value" style="color:#27500A;"><?= $data['funnel']['completed'] ?? 0 ?></p>
    <p class="stat-sub" style="color:#639922;">fully submitted</p>
  </div>
  <div class="stat-card neutral">
    <p class="stat-label">Never Started</p>
    <p class="stat-value" style="color:#791F1F;"><?= $data['funnel']['never_started'] ?? 0 ?></p>
    <p class="stat-sub" style="color:#E24B4A;">unresponsive</p>
  </div>
  <div class="stat-card neutral">
    <p class="stat-label">Abandoned</p>
    <p class="stat-value" style="color:#BA7517;"><?= $data['funnel']['abandoned'] ?? 0 ?></p>
    <p class="stat-sub" style="color:#EF9F27;">in-progress</p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Participation Rate</p>
    <p class="stat-value"><?= $data['funnel']['rates']['completion'] ?? 0 ?>%</p>
    <p class="stat-sub">completion</p>
  </div>
</div>

<!-- ── Year Level Participation ── -->
<span class="section-title"><?= sectionAccent() ?>Year Level Participation</span>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Year Level</th>
        <th class="center">Finished</th>
        <th class="center">Not Finished</th>
        <th class="center">Completion Rate</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data['year_participation'] ?? [] as $item):
        $total = $item['total_finished'] + $item['total_not_finished'];
        $rate  = $total > 0 ? round(($item['total_finished'] / $total) * 100) : 0;
      ?>
      <tr>
        <td style="font-weight:500;"><?= htmlspecialchars($item['year_level']) ?></td>
        <td class="center" style="color:#27500A;font-weight:600;"><?= $item['total_finished'] ?></td>
        <td class="center" style="color:#791F1F;"><?= $item['total_not_finished'] ?></td>
        <td class="center"><?= scoreBar($rate, 100, 80) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ── Category Breakdown ── -->
<span class="section-title"><?= sectionAccent() ?>Category Breakdown</span>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Category</th>
        <th class="center">Average Score</th>
        <th class="center">Visual</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data['category']['category_performance'] ?? [] as $cat): ?>
      <tr>
        <td><?= htmlspecialchars($cat['category']) ?></td>
        <td class="center" style="font-weight:600;"><?= number_format($cat['average_score'], 2) ?></td>
        <td class="center"><?= scoreBar((float)$cat['average_score'], 5.0, 80) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<span class="section-title"><?= sectionAccent() ?>Question Performance</span>
<div class="two-col">
  <!-- Highest -->
  <div class="two-col-cell">
    <p class="col-label high">▲ &nbsp;Top Rated Questions</p>
    <div class="table-wrap" style="border-top:none;border-radius:0 0 8px 8px;">
      <table>
        <thead>
          <tr>
            <th>Question</th>
            <th class="center" style="white-space:nowrap;">Avg.</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['questions']['highest'] ?? [] as $q): ?>
          <tr>
            <td style="font-size:10px;"><?= htmlspecialchars($q['question_text']) ?></td>
            <td class="center" style="font-weight:600;color:#27500A;"><?= number_format($q['average_score'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Lowest -->
  <div class="two-col-cell">
    <p class="col-label low">▼ &nbsp;Needs Improvement</p>
    <div class="table-wrap" style="border-top:none;border-radius:0 0 8px 8px;">
      <table>
        <thead>
          <tr>
            <th>Question</th>
            <th class="center" style="white-space:nowrap;">Avg.</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['questions']['lowest'] ?? [] as $q): ?>
          <tr>
            <td style="font-size:10px;"><?= htmlspecialchars($q['question_text']) ?></td>
            <td class="center" style="font-weight:600;color:#791F1F;"><?= number_format($q['average_score'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Faculty Ranking ── -->
<span class="section-title"><?= sectionAccent() ?>Faculty Ranking</span>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th class="center" style="width:40px;">Rank</th>
        <th style="width:90px;">Employee ID</th>
        <th>Faculty Name</th>
        <th class="center">Evaluators</th>
        <th class="center">Mean Score</th>
        <th class="center">Rating</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data['teachers'] ?? [] as $i => $row):
        $n  = $i + 1;
        $rs = rankStyle($n);
        $rc = ratingColor($row['adjective_rating']);
      ?>
      <tr>
        <td class="center">
          <div style="display: flex; justify-content:center; align-items:center;">
            <span class="rank-circle" style="background:<?= $rs['bg'] ?>;color:<?= $rs['text'] ?>;border-color:<?= $rs['border'] ?>;">
              <?= $n ?>
            </span>
          </div>
        </td>
        <td style="color:#6B6B60;font-size:10px;font-family:monospace;"><?= htmlspecialchars($row['employee_id']) ?></td>
        <td style="font-weight:500;"><?= htmlspecialchars($row['full_name']) ?></td>
        <td class="center"><?= $row['total_evaluated'] ?></td>
        <td class="center">
          <span style="font-weight:600;"><?= number_format($row['mean_score'], 2) ?></span>
          <?= scoreBar((float)$row['mean_score'], 5.0, 50) ?>
        </td>
        <td class="center">
          <span class="rating-pill" style="background:<?= $rc['bg'] ?>;color:<?= $rc['text'] ?>;border-color:<?= $rc['border'] ?>;">
            <?= htmlspecialchars($row['adjective_rating']) ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</div><!-- /body-wrap -->

<!-- ── Footer ── -->
<div class="footer">
  <span class="footer-left">Smart-Eval &nbsp;&middot;&nbsp; Generated <?= date('F j, Y \a\t h:i A') ?></span>
  <span class="footer-right">Asian Institute of Technology and Education &nbsp;&middot;&nbsp; CONFIDENTIAL</span>
</div>

</body>
</html>