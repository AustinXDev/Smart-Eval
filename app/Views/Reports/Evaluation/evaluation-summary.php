<?php

$period = $data['period'] ?? [];

$meanScoreTrend = $data['mean_score_trend'] ?? [];

$currentPeriod = !empty($meanScoreTrend)
    ? $meanScoreTrend[array_key_last($meanScoreTrend)]
    : [];

$average = (float) (
    $period['final_average']
    ?? $currentPeriod['final_average']
    ?? 0
);

$department = $period['target_dept'] ?? '';

$rating = match (true) {
    $average >= 4.21 => 'Outstanding',
    $average >= 3.41 => 'Very Satisfactory',
    $average >= 2.61 => 'Satisfactory',
    $average >= 1.81 => 'Fair',
    default => 'Poor',
};

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

    <?php require __DIR__ . '/style.php'; ?>

</head>

<body>

    <?php require __DIR__ . '/sections/cover.php'; ?>

    <div class="body-wrapper">

        <?php require __DIR__ . '/sections/participation.php'; ?>

        <?php require __DIR__ . '/sections/faculty-ranking.php'; ?>

        <?php require __DIR__ . '/sections/year-participation.php'; ?>

        <?php require __DIR__ . '/sections/category-performance.php'; ?>

        <?php require __DIR__ . '/sections/question-performance.php'; ?>

        <?php require __DIR__ . '/sections/insight.php'; ?>

    </div>

</body>

</html>