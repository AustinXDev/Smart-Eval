<?php
$period = $period;
$rankings = $rankings;
$rating = $rating;
$participation_rate = $participation_rate;

$imagePath = __DIR__ . '/../../public/assets/images/aite-logo.png';
$base64 = base64_encode(file_get_contents($imagePath));

$logo = 'data:image/png;base64,' . $base64;
?>

<html>
<head>
    <style>
        *{
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        body { font-family: sans-serif; color: #333; padding: 2.5rem;}

        .header { text-align: center; margin-bottom: 1rem ; }

        .divider {background-color: #c6c3c3; height: 0.50px; width: 100%; margin-bottom: 1.5rem;};
        
        .schoolName{ font-weight: 100;}

        .summary-container { display: table; width: 100%; margin-bottom: 20px; border-spacing: 8px;}

        .row {
            display: table-row;
            margin-bottom: 0.5rem;
        }

        .t-header{
            white-space: nowrap;
        }

        .t-content{
            margin-top: 0.5rem;
        }

        .card {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table { width: 100%; border-collapse: collapse; page-break-inside: auto;}

        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }

        th { background: #f4f4f4; }

        tr {
            page-break-inside: avoid;
        }

        thead {
            display: table-header-group;
        }

        @page {
            size: A4;
        }

        @media (max-width: 768px) {
          .summary-container {
              display: block;
          }

          .card {
              display: block;
              width: 100%;
              margin-bottom: 10px;
          }

          table {
              font-size: 12px;
          }
        }
    </style>
</head>

<body>

<div class="header">
    <img src="<?= $logo ?>" width="60" style="margin-bottom: 0.5rem;">
    <h2 style="font-size:medium; margin-bottom: 0.2rem" class="schoolName">Asian Institute of Technology and Education</h2>
    <p style="font-size: smaller; color: rgb(94, 94, 94); margin-bottom: 0.5rem;">Gret-Fisico Bldg., Maharlika Highway, Lumingon, Tiaong, Quezon</p>

    <div class="divider" style="margin-bottom: 1rem;"></div>

    <p style="margin-bottom: 0.5rems;margin-bottom: 0.2rem">Faculty Evaluation Summary Report</p>
    <p><span>Academic Year </span> (<?= $period['academic_year'] ?>) - <?= $period['semester'] ?></p>
</div>

<div class="divider"></div>

<h3 style="margin-bottom: 0.5rem;font-size: medium;">Executive Summary</h3>

<div class="summary-container">
    <div class="row">
        <div class="card">
            <strong class="t-header">Participation Rate</strong>
            <p class="t-content" style="color:<?= $participation_rate['color'] ?>;">
                <?= $period['participation_rate'] ?>%
            </p>
        </div>

        <div class="card">
            <strong class="t-header">Non-Participating Students</strong>
            <p class="t-content">
                <?= $period['total_unresponsive_students'] ?>
            </p>
        </div>

        <div class="card">
            <strong class="t-header">Incomplete Submissions</strong>
            <p class="t-content">
                <?= $period['total_incomplete_students'] ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="card">
            <strong class="t-header">Department Mean</strong>
            <p class="t-content">
                <?= $period['final_average'] ?>
            </p>
        </div>

        <div class="card">
            <strong class="t-header">Adjective Rating</strong>
            <p class="t-content" style="color: <?= $rating['color'] ?>">
                <?= $rating['text'] ?>
            </p>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 0.5rem; font-size: medium">Faculty Ranking</h3>

<table>
    <tr>
        <th>Rank</th>
        <th>Faculty Name</th>
        <th>Evaluators</th>
        <th>Mean Score</th>
        <th>Rating</th>
    </tr>

    <?php foreach ($rankings as $i => $row): ?>
        <?php
            $r = ($row['mean_score'] >= 4.5) ? 'Excellent' :
                 ($row['mean_score'] >= 3.5 ? 'Very Good' :
                 ($row['mean_score'] >= 2.5 ? 'Good' : 'Fair'));
        ?>

        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= $row['full_name'] ?></td>
            <td><?= $row['evaluators'] ?></td>
            <td><?= $row['mean_score'] ?></td>
            <td style="color: <?= $rating['color'] ?>;"><?= $r ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="categorical-summary" style="margin-top: 2rem;">
    <h3 style="margin-bottom: 0.5rem; font-size: medium">Category Breakdown</h3>

    <table>
        <tr>
            <th>Category</th>
            <th>Average Score</th>
        </tr>

        <?php foreach ($categories as $row): ?>
            <tr>
                <td><?= $row['category'] ?></td>
                <td><?= $row['cat_avg'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>