<?php

use App\helpers\ReportViewHelpers;

$info = is_array($data['info'] ?? null) ? $data['info'] : [];
$meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
$teacherName = $info['full_name'] ?? $meta['teacher'] ?? 'Teacher';
$employeeId = $info['employee_id'] ?? '';
$academicYear = $meta['period'] ?? $period['academic_year'] ?? '';
$semester = $period['semester'] ?? $meta['semester'] ?? '';

?>

<div class="cover-band">

    <?php if ($logo !== ''): ?>
        <img
            class="cover-logo"
            src="<?= ReportViewHelpers::e($logo) ?>"
            width="48"
        >
    <?php endif; ?>

    <p class="school-name">
        Asian Institute of Technology and Education
    </p>

    <p class="school-addr">
        Gret-Fisico Bldg., Maharlika Highway,
        Lumingon, Tiaong, Quezon
    </p>

    <div class="cover-clearfix"></div>

    <p class="report-title">
        Teacher Individual Evaluation Report
    </p>

    <p class="report-sub">
        Academic Year
        <?= ReportViewHelpers::e($academicYear) ?>

        <?php if ($semester !== ''): ?>
            &nbsp;&mdash;&nbsp;
            <?= ReportViewHelpers::e($semester) ?>
        <?php endif; ?>

        &nbsp;&nbsp;&middot;&nbsp;&nbsp;

        Generated <?= date('F j, Y') ?>
    </p>

</div>

<div class="name-container">

    <div class="faculty-name"><?= ReportViewHelpers::e($teacherName) ?></div>

    <div class="name-subtext">
        <?= $employeeId !== ''
            ? 'Employee ID: ' . ReportViewHelpers::e($employeeId)
            : 'Smart-Eval Evaluation Result' ?>
    </div>

</div>

<div class="cover-rule"></div>