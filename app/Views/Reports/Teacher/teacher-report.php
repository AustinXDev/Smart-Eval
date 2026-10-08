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

  </div>

</body>
</html>
