<?php

use App\helpers\ReportTheme;
use App\helpers\ReportViewHelpers;

?>

<span class="section-title">
    <?= ReportViewHelpers::sectionAccent() ?>
    Category Performance
</span>

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

        <?php foreach (
            $data['category_performance'] ?? [] as $category
        ): ?>

            <?php
            $score = (float) (
                $category['average_score'] ?? 0
            );
            ?>

            <tr>

                <td>
                    <?= ReportViewHelpers::e(
                        $category['category'] ?? ''
                    ) ?>
                </td>

                <td
                    class="center"
                    style="font-weight:600;"
                >
                    <?= ReportViewHelpers::number($score) ?>
                </td>

                <td class="center">

                    <?= ReportViewHelpers::scoreBar(
                        $score,
                        5,
                        80
                    ) ?>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>