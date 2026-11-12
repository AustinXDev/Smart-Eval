<?php

$data = is_array($data ?? null) ? $data : [];
$meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
$period = is_array($data['period'] ?? null)
    ? $data['period']
    : [
        'academic_year' => $meta['period'] ?? '',
        'semester' => $meta['semester'] ?? '',
    ];

$imagePath = BASE_PATH
    . DIRECTORY_SEPARATOR
    . 'public'
    . DIRECTORY_SEPARATOR
    . 'assets'
    . DIRECTORY_SEPARATOR
    . 'images'
    . DIRECTORY_SEPARATOR
    . 'aite-logo.png';

$logo = '';

if (is_file($imagePath)) {
    $base64 = base64_encode(
        file_get_contents($imagePath)
    );

    $logo = 'data:image/png;base64,' . $base64;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  
  <?php require_once __DIR__ . '/style.php'; ?>

</head>

<body>

  <?php require __DIR__ . '/sections/cover.php'; ?>

  <div class="body-wrapper">

    <?php require_once __DIR__ . '/sections/overview.php'; ?>
    <?php require_once __DIR__ . '/sections/category-performance.php'; ?>
    <?php require_once __DIR__ . '/sections/question-gap-analysis.php'; ?>
    <?php require_once __DIR__ . '/sections/insights.php'; ?>

    <div style="margin-top: 24px; page-break-inside: auto;">
      <div class="section-title" style="page-break-after: avoid;">
        <span></span> Student Comments
      </div>

      <?php $comments = is_array($data['comments'] ?? null) ? $data['comments'] : []; ?>
      <?php if ($comments === []): ?>
        <p style="font-size: 10px; color: #626779;">No student comments were provided for this evaluation period.</p>
      <?php else: ?>
        <?php foreach ($comments as $index => $comment): ?>
          <div style="margin-top: 12px; page-break-inside: auto;">
            <div style="font-size: 9px; font-weight: bold; color: #5B21B6; page-break-after: avoid;">
              Comment <?= (int) $index + 1 ?>
            </div>
            <p style="margin: 4px 0 0; font-size: 10px; line-height: 1.5; color: #1A1A2E; word-wrap: break-word;">
              <?= nl2br(\App\helpers\ReportViewHelpers::e((string) $comment)) ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>


  </div>

</body>
</html>
