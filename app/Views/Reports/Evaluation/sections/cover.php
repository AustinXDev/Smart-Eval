<?php

use App\helpers\ReportViewHelpers;

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
        Faculty Evaluation Summary Report
    </p>

    <p class="report-sub">
        Academic Year
        <?= ReportViewHelpers::e($period['academic_year'] ?? '') ?>

        &nbsp;&mdash;&nbsp;

        <?= ReportViewHelpers::e($period['semester'] ?? '') ?>

        &nbsp;&nbsp;&middot;&nbsp;&nbsp;

        Generated <?= date('F j, Y') ?>
    </p>

</div>

<div class="cover-rule"></div>