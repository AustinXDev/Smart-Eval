<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');

require_once __DIR__ . '/../../config/database.php';

$department = $_GET['department'] ?? null;
$req = $_GET['req'] ?? null;

switch($req){
  case 'get_dashboard_data':
    getDashboardData($department, $pdo);
    break;

  case 'get_teacherRanking':
    getTeacherRanking($department, $pdo);
    break;
  
  case 'score_chart':
    getScoreChart($department, $pdo);
    break;
  
  case 'participation_chart': 
    getParticipationChart($department, $pdo);
    break;
  
  case 'program_chart':
    getProgramChart($department, $pdo);
    break;

  case 'get_participation':
    getParticipation($department, $pdo);
    break;
  
  default:
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

//Get Dashboard Card Data
function getDashboardData($department, $pdo){
    $data = [];

    //check active period first
    $periodStmt = $pdo->prepare("
      SELECT period_id
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
      LIMIT 1
    ");
    $periodStmt->execute([$department]);
    $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

    if(!$period){
      echo json_encode([]);
      return;
    }

    //Student total
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM students s
      INNER JOIN programs p
        ON s.program_id = p.program_id
      WHERE p.department = ?
      AND s.is_active = 1
    ');
    $stmt->execute([$department]);
    $data['student_total'] = $stmt->fetchColumn();


    //Teacher Total
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM teachers
      WHERE department = ?
      AND is_active = 1
    ');
    $stmt->execute([$department]);
    $data['teacher_total'] = $stmt->fetchColumn();


    //Evaluation Period
    $stmt = $pdo->prepare('
      SELECT academic_year, semester, start_date, end_date
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
    ');
    $stmt->execute([$department]);
    $data['evaluation_period'] = $stmt->fetch(PDO::FETCH_ASSOC);

    //total student completed
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM students s
      INNER JOIN programs p
        ON s.program_id = p.program_id
      WHERE p.department = ?
      AND s.is_finished_all = 1
      AND s.is_active = 1
    ');
    $stmt->execute([$department]);
    $data['completed_student'] = $stmt->fetchColumn();

    echo json_encode($data);
}


//Get Teacher Ranking
function getTeacherRanking($department, $pdo){

    //get active period
    $periodStmt = $pdo->prepare("
      SELECT period_id
      FROM evaluation_periods 
      WHERE target_dept = ?
      AND is_active = 1
      LIMIT 1
    ");

    $periodStmt->execute([$department]);
    $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

    if(!$period) {
      echo json_encode([]);
      exit;
    }

    $periodId = $period['period_id'];

    // get teacher ranking for active period only
$stmt = $pdo->prepare("
    SELECT 
        t.teacher_id,
        t.full_name AS teacher_name,

        COUNT(DISTINCT expected.student_id) AS total_expected_students,
        COUNT(DISTINCT CASE WHEN s_fin.student_id IS NOT NULL THEN es.student_id END) AS total_evaluated_students,

        ROUND(
            COUNT(DISTINCT CASE WHEN s_fin.student_id IS NOT NULL THEN es.student_id END) * 100.0 /
            NULLIF(COUNT(DISTINCT expected.student_id), 0),
            2
        ) AS participation_rate,

        COALESCE(
            ROUND(
                NULLIF(SUM(student_means.answer_sum), 0) /
                NULLIF(
                    COUNT(DISTINCT CASE WHEN s_fin.student_id IS NOT NULL THEN es.student_id END) * (
                        SELECT COUNT(*)
                        FROM questions q
                        INNER JOIN evaluation_periods ep ON ep.set_id = q.set_id
                        WHERE ep.period_id = ?
                        AND q.is_active = 1
                    ),
                0),
            2),
            0
        ) AS overall_mean_score

    FROM teachers t

    INNER JOIN teacher_load tl ON tl.teacher_id = t.teacher_id
    INNER JOIN programs p ON p.program_id = tl.program_id

    INNER JOIN (
        -- loads that have evaluation_status rows this period (covers irregular)
        SELECT DISTINCT load_id
        FROM evaluation_status
        WHERE period_id = ?

        UNION

        -- loads that have matching regular students (covers loads with no submissions yet)
        SELECT DISTINCT tl_a.load_id
        FROM teacher_load tl_a
        INNER JOIN students s_a
            ON  s_a.program_id = tl_a.program_id
            AND s_a.year_level = tl_a.year_level
            AND s_a.is_active  = 1
            AND (s_a.enrollment_type = 'Regular' OR s_a.enrollment_type IS NULL)
        INNER JOIN programs p_a ON p_a.program_id = tl_a.program_id
        WHERE p_a.department = ?
    ) AS active_loads ON active_loads.load_id = tl.load_id

    LEFT JOIN (
        -- Regular: match by program + year_level
        SELECT 
            s.student_id, 
            s.program_id, 
            s.year_level,
            NULL AS load_id,
            'regular' AS match_type
        FROM students s
        WHERE (s.enrollment_type = 'Regular' OR s.enrollment_type IS NULL)
        AND s.is_active = 1

        UNION ALL

        -- Irregular: match only by explicit load assignment this period
        SELECT DISTINCT
            s2.student_id, 
            s2.program_id, 
            s2.year_level,
            es_irr.load_id,
            'irregular' AS match_type
        FROM evaluation_status es_irr
        INNER JOIN students s2 ON s2.student_id = es_irr.student_id
        WHERE es_irr.period_id = ?
        AND s2.enrollment_type = 'Irregular'
    ) AS expected
    ON (
        -- Regular: match by program + year_level
        (
            expected.match_type = 'regular' 
            AND expected.program_id = tl.program_id 
            AND expected.year_level = tl.year_level
        )
        OR
        -- Irregular: match by explicit load_id only
        (
            expected.match_type = 'irregular' 
            AND expected.load_id = tl.load_id
        )
    )

    LEFT JOIN evaluation_status es
        ON  es.load_id      = tl.load_id
        AND es.student_id   = expected.student_id
        AND es.period_id    = ?
        AND es.is_submitted = 1

    -- finished = submitted all assigned loads this period
    LEFT JOIN (
        SELECT es_done.student_id
        FROM evaluation_status es_done
        WHERE es_done.period_id = ?
        AND es_done.is_submitted = 1
        GROUP BY es_done.student_id
        HAVING COUNT(es_done.load_id) = (
            SELECT COUNT(*)
            FROM evaluation_status es_total
            WHERE es_total.student_id = es_done.student_id
            AND es_total.period_id    = ?
        )
    ) AS s_fin ON s_fin.student_id = es.student_id

    -- scores from fully finished students only
    LEFT JOIN (
        SELECT 
            ea.eval_id,
            SUM(ea.score)   AS answer_sum,
            COUNT(ea.score) AS answer_count
        FROM evaluation_answers ea
        INNER JOIN evaluation_status es_scope
            ON  es_scope.eval_id      = ea.eval_id
            AND es_scope.period_id    = ?
            AND es_scope.is_submitted = 1
        INNER JOIN (
            SELECT es_done.student_id
            FROM evaluation_status es_done
            WHERE es_done.period_id = ?
            AND es_done.is_submitted = 1
            GROUP BY es_done.student_id
            HAVING COUNT(es_done.load_id) = (
                SELECT COUNT(*)
                FROM evaluation_status es_total
                WHERE es_total.student_id = es_done.student_id
                AND es_total.period_id    = ?
            )
        ) AS finished_students ON finished_students.student_id = es_scope.student_id
        GROUP BY ea.eval_id
    ) AS student_means ON student_means.eval_id = es.eval_id

    WHERE p.department = ?

    GROUP BY
        t.teacher_id,
        t.full_name

    HAVING overall_mean_score > 0
    ORDER BY overall_mean_score DESC
    LIMIT 5;
");

$stmt->execute([
    $periodId,   
    $periodId,  
    $department, 
    $periodId,   
    $periodId,   
    $periodId,   
    $periodId,   
    $periodId,   
    $periodId,   
    $periodId,   
    $department 
]);


  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($result);
}

//Get Score Chart Data
function getScoreChart($department, $pdo){
    
    //get active period
    $periodStmt = $pdo->prepare("
      SELECT period_id
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
      LIMIT 1
    ");
    $periodStmt->execute([$department]);
    $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

    if(!$period){
      echo json_encode([]);
      return;
    }

    $periodId = $period['period_id'];

    $stmt = $pdo->prepare("
      SELECT 
        ea.score, 
        COUNT(ea.score) AS total_count
      FROM evaluation_answers ea

      INNER JOIN evaluation_status es
        ON es.eval_id = ea.eval_id
        AND es.period_id = ?
        AND es.is_submitted = 1

      INNER JOIN teacher_load tl
        ON tl.load_id = es.load_id

      INNER JOIN programs p 
        ON p.program_id = tl.program_id
        AND p.department = ?

      GROUP BY ea.score
      ORDER BY ea.score DESC
    ");

    $stmt->execute([$periodId, $department]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $distribution = [
      5 => 0,
      4 => 0,
      3 => 0,
      2 => 0,
      1 => 0,
    ];

    foreach($rows as $row){
      $distribution[(int)$row['score']] = (int)$row['total_count'];
    }

    echo json_encode([
      'labels' => ['Score 5', 'Score 4', 'Score 3', 'Score 2', 'Score 1'],
      'data' => [
        $distribution[5],
        $distribution[4],
        $distribution[3],
        $distribution[2],
        $distribution[1],
      ]
    ]);
}

//Get Particpation Chart Data
function getParticipationChart($department, $pdo){

    //check active period first
    $periodStmt = $pdo->prepare("
      SELECT period_id
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
      LIMIT 1
    ");
    $periodStmt->execute([$department]);
    $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

    if(!$period){
      echo json_encode([]);
      return;
    }

    $stmt = $pdo->prepare("
      SELECT 
        SUM(CASE WHEN s.is_finished_all = 1 THEN 1 ELSE 0 END) AS finished,
        SUM(CASE WHEN s.is_finished_all = 0 THEN 1 ELSE 0 END) AS not_finished,
        COUNT(*) AS total_evaluators
        FROM students s
        INNER JOIN programs p
          ON p.program_id = s.program_id
          AND p.department = ?
        WHERE s.is_active = 1
    ");

    $stmt->execute([$department]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
      'labels' => ['Completed', 'Pending'],
      'data' => [
        (int) $row['finished'],
        (int) $row['not_finished'],
      ],
      'total' => (int) $row['total_evaluators'],
    ]);
}

//Get Program Chart Data
function getProgramChart($department, $pdo){

    //check active period first
    $periodStmt = $pdo->prepare("
      SELECT period_id
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
      LIMIT 1
    ");
    $periodStmt->execute([$department]);
    $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

    if(!$period){
      echo json_encode([]);
      return;
    }


    $stmt = $pdo->prepare("
      SELECT 
        p.program_name,
        COUNT(s.student_id) AS total_students,
        SUM(CASE WHEN s.is_finished_all = 1 THEN 1 ELSE 0 END) AS finished,
        SUM(CASE WHEN s.is_finished_all = 0 THEN 1 ELSE 0 END) AS not_finished
      FROM programs p
      LEFT JOIN students s
        ON s.program_id = p.program_id
        AND s.is_active = 1
      WHERE p.department = ?
      AND p.is_active = 1
      GROUP BY p.program_id, p.program_name
      ORDER BY p.program_name ASC
    ");

    $stmt->execute([$department]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $finished = [];
    $not_finished = [];
    $totals = [];

    foreach($rows as $row){
      $labels[] = $row['program_name'];
      $finished[] = (int) $row['finished'];
      $not_finished[] = (int) $row['not_finished'];
      $totals[] = (int) $row['total_students'];
    }

    echo json_encode([
      'labels' => $labels,
      'finished' => $finished,
      'not_finished' => $not_finished,
      'totals' => $totals,
    ]);
}

function getParticipation($department, $pdo){
  
  //get active period
  $periodStmt = $pdo->prepare("
    SELECT period_id
    FROM evaluation_periods
    WHERE target_dept = ?
    AND is_active = 1
    LIMIT 1
  ");
  $periodStmt->execute([$department]);
  $period = $periodStmt->fetch(PDO::FETCH_ASSOC);

  if(!$period){
    echo json_encode([]);
    return;
  }

  $periodId = $period['period_id'];

  $stmt = $pdo->prepare("
    SELECT
      COUNT(DISTINCT es.student_id) AS total,
      COUNT(DISTINCT CASE WHEN fin.student_id IS NOT NULL THEN es.student_id END) AS finished,
      COUNT(DISTINCT CASE WHEN fin.student_id IS NULL THEN es.student_id END) AS not_finished
    FROM evaluation_status es
    INNER JOIN students s ON s.student_id = es.student_id
    INNER JOIN programs p ON p.program_id = s.program_id
    LEFT JOIN (
      SELECT student_id
      FROM evaluation_status
      WHERE period_id = ?
      AND is_submitted = 1
      GROUP BY student_id
      HAVING COUNT(load_id) = (
        SELECT COUNT(*) FROM evaluation_status es2
        WHERE es2.student_id = evaluation_status.student_id
        AND es2.period_id = ?
      )
    ) AS fin ON fin.student_id = es.student_id
    WHERE es.period_id = ?
    AND p.department = ?
  ");
  $stmt->execute([$periodId, $periodId, $periodId, $department]);
  $current = $stmt->fetch(PDO::FETCH_ASSOC);

  $prevStmt = $pdo->prepare("
    SELECT period_id
    FROM evaluation_periods
    WHERE target_dept = ?
    AND is_active = 0
    ORDER BY end_date DESC
    LIMIT 1
  ");
  $prevStmt->execute([$department]);
  $prevPeriod = $prevStmt->fetch(PDO::FETCH_ASSOC);

  $finished_change = null;
  $is_up = true;

  if($prevPeriod){
    $prevId = $prevPeriod['period_id'];

    // previous period finished count from evaluation_status history
    $prevStmt2 = $pdo->prepare("
      SELECT
        COUNT(DISTINCT CASE WHEN fin.student_id IS NOT NULL THEN es.student_id END) AS prev_finished
      FROM evaluation_status es
      INNER JOIN students s ON s.student_id = es.student_id
      INNER JOIN programs p ON p.program_id = s.program_id
      LEFT JOIN (
        SELECT student_id
        FROM evaluation_status
        WHERE period_id = ?
        AND is_submitted = 1
        GROUP BY student_id
        HAVING COUNT(load_id) = (
          SELECT COUNT(*) FROM evaluation_status es2
          WHERE es2.student_id = evaluation_status.student_id
          AND es2.period_id = ?
        )
      ) AS fin ON fin.student_id = es.student_id
      WHERE es.period_id = ?
      AND p.department = ?
    ");

    $prevStmt2->execute([$prevId, $prevId, $prevId, $department]);
    $prev = $prevStmt2->fetch(PDO::FETCH_ASSOC);

    $prevFinished = (int) $prev['prev_finished'];

    if($prevFinished > 0){
      $change = (((int)$current['finished'] - $prevFinished) / $prevFinished) * 100;
      $finished_change = round(abs($change), 1);
      $is_up = $change >= 0;
    }
  }

  echo json_encode([
    'finished'        => (int) $current['finished'],
    'not_finished'    => (int) $current['not_finished'],
    'total'           => (int) $current['total'],
    'finished_change' => $finished_change,
    'is_up'           => $is_up,
  ]);
}
?>