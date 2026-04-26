<?php
$imagePath = __DIR__ . '/../../public/assets/images/aite-logo.png';
$base64    = base64_encode(file_get_contents($imagePath));
$logo      = 'data:image/png;base64,' . $base64;

define('CLR_PURPLE_DARK',   '#2D1B69');
define('CLR_PURPLE',        '#3C3489');
define('CLR_PURPLE_MID',    '#534AB7');
define('CLR_PURPLE_LIGHT',  '#EEEDFE');
define('CLR_PURPLE_BORDER', '#AFA9EC');

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

function scoreBar(float $score, float $max = 5.0, int $width = 80): string {
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
          <span style='font-size:9px;color:#9E9A93;margin-left:3px;'>{$pct}%</span>
        </div>
    ";
}

function sectionAccent(): string {
    return "<div style='width:3px;height:14px;background:" . CLR_PURPLE_MID . ";border-radius:2px;display:inline-block;vertical-align:middle;margin-right:7px;'></div>";
}

// ── Aliases ───────────────────────────────────────────────
$info      = $data['info'];
$breakdown = $data['breakdown'];
$weakest   = $data['question_gaps']['weakest']  ?? [];
$strongest = $data['question_gaps']['strongest'] ?? [];

$adjectiveRating = is_array($info['adjective_rating'])
    ? ($info['adjective_rating']['rating'] ?? '')
    : ($info['adjective_rating'] ?? '');

$rc = ratingColor($adjectiveRating);

$meta = array_merge([
    'academic_year' => 'N/A',
    'semester'      => 'N/A',
    'generated_at'  => date('F j, Y g:i A'),
    'is_live'       => false,
    'teacher_name'  => $info['full_name'] ?? 'N/A',
], $meta ?? []);
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

  /* ── Page break controls ── */
  .section-block       { page-break-inside:avoid; break-inside:avoid; }
  .section-title       { page-break-after:avoid;  break-after:avoid;  }
  .stat-row, .two-col,
  .table-wrap          { page-break-inside:avoid; break-inside:avoid; }
  .page-break-before   { page-break-before:always !important; break-before:always !important; }
  thead                { display:table-header-group; }
  tr                   { page-break-inside:avoid !important; break-inside:avoid !important; }

  /* ── Cover band ── */
  .cover-band          { background:<?= CLR_PURPLE_DARK ?>; padding:28px 36px 24px; page-break-inside:avoid; break-inside:avoid; }
  .cover-band .school-name  { font-size:13px; font-weight:700; color:#fff; margin-bottom:2px; }
  .cover-band .school-addr  { font-size:10px; color:<?= CLR_PURPLE_BORDER ?>; margin-bottom:16px; }
  .cover-band .report-title { font-size:17px; font-weight:700; color:#fff; margin-bottom:3px; letter-spacing:-0.3px; }
  .cover-band .report-sub   { font-size:11px; color:<?= CLR_PURPLE_BORDER ?>; }
  .cover-logo    { float:right; margin-top:-4px; }
  .cover-clearfix { clear:both; }

  /* ── Teacher strip ── */
  .teacher-strip        { background:<?= CLR_PURPLE_MID ?>; padding:14px 36px; display:table; width:100%; }
  .teacher-strip .t-name { font-size:15px; font-weight:700; color:#fff; display:table-cell; vertical-align:middle; }
  .teacher-strip .t-meta { font-size:10px; color:<?= CLR_PURPLE_BORDER ?>; display:table-cell; text-align:right; vertical-align:middle; }

  .cover-rule { height:3px; background:linear-gradient(90deg,<?= CLR_PURPLE_MID ?>,<?= CLR_PURPLE_BORDER ?>,#fff); }

  /* ── Body ── */
  .body-wrap { padding:22px 36px 32px; }

  /* ── Section titles ── */
  .section-title {
    font-size:10px; font-weight:600;
    color:<?= CLR_PURPLE ?>;
    text-transform:uppercase; letter-spacing:0.08em;
    margin-bottom:10px; margin-top:22px;
    padding-bottom:6px;
    border-bottom:0.5px solid <?= CLR_PURPLE_BORDER ?>;
    display:block;
  }

  /* ── Hero ── */
  .hero-row  { display:table; width:100%; border-spacing:10px; margin-bottom:4px; }
  .hero-main {
    display:table-cell; width:35%;
    background:<?= CLR_PURPLE_DARK ?>; border-radius:10px;
    padding:20px 18px; text-align:center; vertical-align:middle;
  }
  .hero-main .score-num   { font-size:42px; font-weight:700; color:#fff; line-height:1; }
  .hero-main .score-denom { font-size:13px; color:<?= CLR_PURPLE_BORDER ?>; margin-top:3px; }
  .hero-main .rating-badge {
    display:inline-block; margin-top:10px; padding:4px 14px; border-radius:999px;
    font-size:11px; font-weight:700;
    background:<?= $rc['bg'] ?>; color:<?= $rc['text'] ?>; border:0.5px solid <?= $rc['border'] ?>;
  }
  .hero-side  { display:table-cell; vertical-align:top; }
  .side-cards { display:table; width:100%; border-spacing:7px; }
  .side-card  {
    display:table-cell;
    background:<?= CLR_PURPLE_LIGHT ?>;
    border:0.5px solid <?= CLR_PURPLE_BORDER ?>;
    border-top:2.5px solid <?= CLR_PURPLE_MID ?>;
    border-radius:7px; padding:10px 12px; vertical-align:top;
  }
  .side-card .sc-label { font-size:9px; font-weight:600; color:<?= CLR_PURPLE ?>; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:5px; }
  .side-card .sc-value { font-size:20px; font-weight:700; color:<?= CLR_PURPLE_DARK ?>; }
  .side-card .sc-sub   { font-size:9px; color:<?= CLR_PURPLE_MID ?>; margin-top:2px; }

  /* ── Tables ── */
  table { width:100%; border-collapse:collapse; page-break-inside:auto; }
  th {
    background:<?= CLR_PURPLE_DARK ?>; padding:8px 10px; text-align:left;
    font-size:9px; font-weight:600; color:<?= CLR_PURPLE_BORDER ?>;
    text-transform:uppercase; letter-spacing:0.06em; border-bottom:none;
  }
  th.center, td.center { text-align:center; }
  td { padding:8px 10px; font-size:11px; color:#1A1714; border-bottom:0.5px solid #EEECE7; vertical-align:middle; }
  tr:last-child td      { border-bottom:none; }
  tr:nth-child(even) td { background:#FAFAF8; }
  .table-wrap { border:0.5px solid <?= CLR_PURPLE_BORDER ?>; border-radius:8px; overflow:hidden; }

  /* ── Two-col ── */
  .two-col      { display:table; width:100%; border-spacing:10px; }
  .two-col-cell { display:table-cell; width:50%; vertical-align:top; }
  .col-label    { font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:4px 4px 0 0; }
  .col-label.strong { background:#EAF3DE; color:#27500A; }
  .col-label.weak   { background:#FCEBEB; color:#791F1F; }

  /* ── Gap colors ── */
  .gap-pos { color:#27500A; font-weight:700; font-size:10px; }
  .gap-neg { color:#791F1F; font-weight:700; font-size:10px; }

  /* ── Confidential note ── */
  .confidential-note {
    text-align:center; font-size:9px; color:<?= CLR_PURPLE_BORDER ?>;
    letter-spacing:0.1em; text-transform:uppercase;
    margin-bottom:14px; padding:4px 0;
    border-top:0.5px solid <?= CLR_PURPLE_LIGHT ?>;
    border-bottom:0.5px solid <?= CLR_PURPLE_LIGHT ?>;
  }

  /* ── Footer ── */
  .footer {
    margin-top:2.5rem; padding:10px 36px;
    border-top:2px solid <?= CLR_PURPLE_DARK ?>;
    display:table; width:100%;
    background:<?= CLR_PURPLE_LIGHT ?>;
    page-break-inside:avoid; break-inside:avoid;
  }
  .footer-left  { display:table-cell; font-size:9px; color:<?= CLR_PURPLE ?>; font-weight:500; }
  .footer-right { display:table-cell; text-align:right; font-size:9px; color:<?= CLR_PURPLE_MID ?>; }

  @page { size:A4; margin:0; orphans:3; widows:3; }
  @media print { body { padding:0; } }
</style>
</head>
<body>

<!-- ── Cover ── -->
<div class="cover-band">
  <img class="cover-logo" src="<?= $logo ?>" width="46">
  <p class="school-name">Asian Institute of Technology and Education</p>
  <p class="school-addr">Gret-Fisico Bldg., Maharlika Highway, Lumingon, Tiaong, Quezon</p>
  <div class="cover-clearfix"></div>
  <p class="report-title">Individual Faculty Evaluation Report</p>
  <p class="report-sub">
    Academic Year <?= htmlspecialchars($meta['academic_year']) ?>
    &nbsp;&mdash;&nbsp;
    <?= htmlspecialchars($meta['semester']) ?>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    Generated <?= htmlspecialchars($meta['generated_at']) ?>
  </p>
</div>

<!-- ── Teacher strip ── -->
<div class="teacher-strip">
  <span class="t-name"><?= htmlspecialchars($info['full_name'] ?? 'N/A') ?></span>
  <span class="t-meta">
    <?php if (!empty($info['employee_id'])): ?>
      Employee ID: <?= htmlspecialchars($info['employee_id']) ?> &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <?php endif; ?>
    Faculty Evaluation Report
  </span>
</div>

<div class="cover-rule"></div>

<div class="body-wrap">

  <div class="confidential-note">Confidential &nbsp;&middot;&nbsp; For Administrative Use Only</div>

  <!-- ── Hero: Overall Score ── -->
  <div class="section-block">
    <span class="section-title"><?= sectionAccent() ?>Overall Performance</span>

    <div class="hero-row">

      <div class="hero-main">
        <p class="score-num"><?= number_format((float)$info['average_score'], 2) ?></p>
        <p class="score-denom">out of 5.00</p>
        <!-- FIX: use extracted string $adjectiveRating -->
        <span class="rating-badge"><?= htmlspecialchars($adjectiveRating) ?></span>
      </div>

      <div class="hero-side">
        <div class="side-cards">
          <div class="side-card">
            <p class="sc-label">Total Evaluators</p>
            <p class="sc-value"><?= (int)($info['total_evaluated'] ?? 0) ?></p>
            <p class="sc-sub">students responded</p>
          </div>
          <div class="side-card">
            <p class="sc-label">Score Range</p>
            <p class="sc-value" style="font-size:14px;">1.00 – 5.00</p>
            <p class="sc-sub">Likert scale</p>
          </div>
        </div>

        <!-- Score visual bar -->
        <div style="margin-top:10px;background:<?= CLR_PURPLE_LIGHT ?>;border:0.5px solid <?= CLR_PURPLE_BORDER ?>;border-radius:8px;padding:12px 14px;">
          <p style="font-size:9px;font-weight:600;color:<?= CLR_PURPLE ?>;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">Score Visual</p>
          <div style="width:100%;height:10px;background:#DEDAD4;border-radius:5px;overflow:hidden;">
            <?php $heroPct = round(((float)$info['average_score'] / 5) * 100); ?>
            <div style="width:<?= $heroPct ?>%;height:100%;background:<?= $rc['border'] ?>;border-radius:5px;"></div>
          </div>
          <div style="display:table;width:100%;margin-top:5px;">
            <span style="display:table-cell;font-size:9px;color:#9E9A93;">1.00</span>
            <span style="display:table-cell;text-align:center;font-size:9px;font-weight:600;color:<?= CLR_PURPLE ?>;"><?= number_format((float)$info['average_score'], 2) ?></span>
            <span style="display:table-cell;text-align:right;font-size:9px;color:#9E9A93;">5.00</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Category Breakdown ── -->
  <div class="section-block">
    <span class="section-title"><?= sectionAccent() ?>Category Breakdown</span>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Category</th>
            <th class="center">Average Score</th>
            <th class="center">Visual</th>
            <th class="center">vs. Overall</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($breakdown as $cat):
            $catAvg  = (float)$cat['avg_score'];
            $overall = (float)$info['average_score'];
            $gap     = $catAvg - $overall;
            $gapCls  = $gap >= 0 ? 'gap-pos' : 'gap-neg';
            $gapLbl  = ($gap >= 0 ? '+' : '') . number_format($gap, 2);
          ?>
          <tr>
            <td style="font-weight:500;"><?= htmlspecialchars($cat['category']) ?></td>
            <td class="center" style="font-weight:600;"><?= number_format($catAvg, 2) ?></td>
            <td class="center"><?= scoreBar($catAvg, 5.0, 90) ?></td>
            <td class="center"><span class="<?= $gapCls ?>"><?= $gapLbl ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Question Gap Analysis ── -->
  <div class="section-block page-break-before">
    <span class="section-title"><?= sectionAccent() ?>Question Gap Analysis</span>

    <div class="two-col">

      <!-- Strongest -->
      <div class="two-col-cell">
        <p class="col-label strong">&#9650; &nbsp;Strongest Questions</p>
        <div class="table-wrap" style="border-top:none;border-radius:0 0 8px 8px;">
          <table>
            <thead>
              <tr>
                <th>Question</th>
                <th class="center">Score</th>
                <th class="center">Gap</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($strongest)): ?>
              <tr><td colspan="3" style="text-align:center;color:#9E9A93;font-size:10px;">No data available</td></tr>
              <?php else: ?>
              <?php foreach ($strongest as $q):
                $qGap    = (float)$q['gap'];
                $gapLbl  = ($qGap >= 0 ? '+' : '') . number_format($qGap, 2);
              ?>
              <tr>
                <td style="font-size:10px;line-height:1.4;"><?= htmlspecialchars($q['question_text']) ?></td>
                <td class="center" style="font-weight:600;color:#27500A;"><?= number_format((float)$q['q_avg'], 2) ?></td>
                <td class="center <?= $qGap >= 0 ? 'gap-pos' : 'gap-neg' ?>"><?= $gapLbl ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Weakest -->
      <div class="two-col-cell">
        <p class="col-label weak">&#9660; &nbsp;Needs Improvement</p>
        <div class="table-wrap" style="border-top:none;border-radius:0 0 8px 8px;">
          <table>
            <thead>
              <tr>
                <th>Question</th>
                <th class="center">Score</th>
                <th class="center">Gap</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($weakest)): ?>
              <tr><td colspan="3" style="text-align:center;color:#9E9A93;font-size:10px;">No data available</td></tr>
              <?php else: ?>
              <?php foreach ($weakest as $q):
                $qGap   = (float)$q['gap'];
                $gapLbl = ($qGap >= 0 ? '+' : '') . number_format($qGap, 2);
              ?>
              <tr>
                <td style="font-size:10px;line-height:1.4;"><?= htmlspecialchars($q['question_text']) ?></td>
                <td class="center" style="font-weight:600;color:#791F1F;"><?= number_format((float)$q['q_avg'], 2) ?></td>
                <td class="center <?= $qGap >= 0 ? 'gap-pos' : 'gap-neg' ?>"><?= $gapLbl ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Gap legend -->
    <div style="margin-top:10px;padding:8px 12px;background:<?= CLR_PURPLE_LIGHT ?>;border:0.5px solid <?= CLR_PURPLE_BORDER ?>;border-radius:6px;">
      <p style="font-size:9px;color:<?= CLR_PURPLE ?>;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Gap Legend</p>
      <p style="font-size:10px;color:#6B6B60;line-height:1.5;">
        Gap = Question Score &minus; Overall Mean (<?= number_format((float)$info['average_score'], 2) ?>).
        &nbsp;<span style="color:#27500A;font-weight:700;">Positive (+)</span> means above average.
        &nbsp;<span style="color:#791F1F;font-weight:700;">Negative (&minus;)</span> means below average &mdash; priority for improvement.
      </p>
    </div>
  </div>

</div>

<!-- ── Footer ── -->
<div class="footer">
  <span class="footer-left">
    Smart-Eval &nbsp;&middot;&nbsp; <?= htmlspecialchars($meta['generated_at']) ?>
  </span>
  <span class="footer-right">
    <?= htmlspecialchars($info['full_name'] ?? 'N/A') ?> &nbsp;&middot;&nbsp; CONFIDENTIAL
  </span>
</div>

</body>
</html>