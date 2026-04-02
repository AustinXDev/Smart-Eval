<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$period_id = $_POST['period_id'];

if(!$period_id){
  echo json_encode(['status' => 'error', 'message' => 'period id not found.']);
  exit;
}

try{
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
      SELECT period_id, target_dept, is_active
      FROM evaluation_periods
      WHERE period_id = ?
  ");
  $stmt->execute([$period_id]);
  $period = $stmt->fetch();

  if (!$period) {
      throw new Exception("Period not found.");
  }

  $dept = $period['target_dept'];

  //count total expected evaluations
   $stmt = $pdo->prepare("
      SELECT COUNT(*) AS total_expected
      FROM teacher_load tl
      JOIN teachers t
          ON tl.teacher_id = t.teacher_id
          AND t.is_active = 1
      JOIN students s
          ON s.program_id = tl.program_id
          AND s.year_level = tl.year_level
          AND s.is_active = 1
      JOIN programs p
          ON s.program_id = p.program_id
      WHERE p.department = ?
  ");
  $stmt->execute([$dept]);
  $totalExpected = (int)$stmt->fetchColumn();

  if ($totalExpected === 0) {
      throw new Exception("No expected evaluations found for this period.");
  }

  //count submitted evalutions
  $stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_submitted
    FROM evaluation_status es
    JOIN students s
        ON es.student_id = s.student_id
        AND s.is_active = 1
    JOIN teacher_load tl
        ON tl.program_id = s.program_id
        AND tl.year_level = s.year_level
    JOIN teachers t
        ON tl.teacher_id = t.teacher_id
        AND t.is_active = 1
    JOIN programs p
        ON s.program_id = p.program_id
    WHERE es.period_id = ?
        AND es.is_submitted = 1
        AND p.department = ?
    ");
    $stmt->execute([$period_id, $dept]);
    $totalSubmitted = (int)$stmt->fetchColumn();

  //calculate participation rate
  $participationRate = ($totalSubmitted / $totalExpected) * 100;

  //check if participation is 100%
  if ($participationRate < 100) {
      throw new Exception("Cannot force close. Participation is only {$participationRate}%.");
  }

  //force_closed
  $stmt = $pdo->prepare("UPDATE evaluation_periods SET is_active = 0, is_closed = 1 WHERE period_id = ?");
  $stmt->execute([$period_id]);

  $pdo->commit();
  echo json_encode([
      'status' => 'success',
      'message' => 'Evaluation period successfully closed. Participation: ' . number_format($participationRate, 2) . '%'
  ]);
}
catch(Exception $e){
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>