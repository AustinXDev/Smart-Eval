<?php

use App\helpers\ReportTheme;
use App\helpers\ReportViewHelpers;

?>

<span class="section-title">
    <?= ReportViewHelpers::sectionAccent() ?>
    Year Level Participation
</span>

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

        <?php foreach (
            $data['year_participation'] ?? [] as $item
        ): ?>

            <?php

            $finished = (int) (
                $item['total_finished'] ?? 0
            );

            $notFinished = (int) (
                $item['total_not_finished'] ?? 0
            );

            $total = $finished + $notFinished;

            $rate = $total > 0
                ? round(($finished / $total) * 100)
                : 0;

            ?>

            <tr>

                <td style="font-weight:500;">
                    <?= ReportViewHelpers::e(
                        $item['year_level'] ?? ''
                    ) ?>
                </td>

                <td
                    class="center"
                    style="color:#27500A;font-weight:600;"
                >
                    <?= $finished ?>
                </td>

                <td
                    class="center"
                    style="color:#791F1F;"
                >
                    <?= $notFinished ?>
                </td>

                <td class="center">
                    <?= ReportViewHelpers::scoreBar(
                        $rate,
                        100,
                        80
                    ) ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>