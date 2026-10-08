<?php

use App\helpers\ReportViewHelpers;
use App\helpers\AnalyticsHelpers;

?>

<span class="section-title">
    <?= ReportViewHelpers::sectionAccent() ?>
    Faculty Ranking
</span>

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
        <?php foreach ($data['teacher_ranking'] ?? [] as $index => $teacher): ?>
            <?php
            $rank = $index + 1;
            $meanScore = (float) ($teacher['mean_score'] ?? 0);

            $adjectiveRating = AnalyticsHelpers::adjectiveRating($meanScore);
            $rankStyle       = ReportViewHelpers::rankStyle($rank);
            $ratingStyle     = ReportViewHelpers::ratingColor($adjectiveRating);
            ?>

            <tr>
                <td class="center">
                    <span
                        class="rank-circle"
                        style="
                            background:<?= $rankStyle['bg'] ?>;
                            color:<?= $rankStyle['text'] ?>;
                            border-color:<?= $rankStyle['border'] ?>;
                        "
                    >
                        <?= $rank ?>
                    </span>
                </td>

                <td style="color:#6B6B60; font-size:10px; font-family:monospace;">
                    <?= ReportViewHelpers::e($teacher['employee_id'] ?? '') ?>
                </td>

                <td style="font-weight:500;">
                    <?= ReportViewHelpers::e($teacher['full_name'] ?? '') ?>
                </td>

                <td class="center">
                    <?= (int) ($teacher['total_evaluated'] ?? 0) ?>
                </td>

                <td class="center">
                    <span style="font-weight:600;">
                        <?= ReportViewHelpers::number($meanScore) ?>
                    </span>
                    <?= ReportViewHelpers::scoreBar($meanScore, 5, 50) ?>
                </td>

                <td class="center">
                    <span
                        class="rating-pill"
                        style="
                            background:<?= $ratingStyle['bg'] ?>;
                            color:<?= $ratingStyle['text'] ?>;
                            border-color:<?= $ratingStyle['border'] ?>;
                        "
                    >
                        <?= ReportViewHelpers::e($adjectiveRating) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>