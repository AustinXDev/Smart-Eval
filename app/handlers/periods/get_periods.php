<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

function getPeriods($pdo){
  global $pdo;

  $stmt = $pdo->prepare("SELECT * FROM evaluation_periods ORDER BY start_date DESC");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
  $periods = getPeriods($pdo);
  echo json_encode(['status' => 'success', 'periods' => $periods]);
} catch (PDOException $e){
  echo json_encode(['status'=>'error', 'message'=>'Database error:' . $e->getMessage()]);
  exit;
}

?>