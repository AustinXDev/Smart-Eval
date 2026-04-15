<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';
date_default_timezone_set('Asia/Manila');

$period_id = $_POST['period_id'] ?? '';
$now = date('Y-m-d H:i:s');


if(!$period_id){
  echo json_encode(['status' => 'error', 'message' => 'Invalid Period.']);
  exit;
}

try{
  $pdo->beginTransaction();

  //get department
  $stmt = $pdo->prepare("
    SELECT target_dept 
    FROM evaluation_periods 
    WHERE period_id = ?
  ");
  $stmt->execute([$period_id]);
  $period = $stmt->fetch();

  if(!$period){
    throw new Exception('Period not found.');
  }

  $dept = $period['target_dept'];

  //check if there is already active period
  $check = $pdo->prepare("
    SELECT COUNT(*)
    FROM evaluation_periods
    WHERE target_dept = ?
    AND is_active = 1
  ");
  $check->execute([$dept]);
  $hasActive = $check->fetchColumn();

  if($hasActive > 0){
    echo json_encode([
      'status' => 'error',
      'message' => 'Another evaluation period is already active.'
    ]);
    $pdo->rollBack();
    exit;
  } 

  //find the closest
  $stmt = $pdo->prepare("
        SELECT period_id, start_date 
        FROM evaluation_periods
        WHERE target_dept = ? AND start_date >= ?
        ORDER BY start_date ASC
        LIMIT 1
  ");
  $stmt->execute([$dept, $now]);
  $closest = $stmt->fetch();

  if(!$closest){
      throw new Exception('No upcoming period to activate.');
  }

  if($period_id != $closest['period_id']){
     throw new Exception('You can only activate the period closest to today.');
  }

  $activate = $pdo->prepare("UPDATE evaluation_periods SET is_active = 1, is_forced = 1 WHERE period_id = ?");
  $activate->execute([$period_id]);

  $pdo->commit();

  echo json_encode(['status' => 'success', 'message' => 'Period force-activated successfully']);
}
catch(Exception $e) {
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>