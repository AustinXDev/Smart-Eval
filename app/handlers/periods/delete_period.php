<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$period_id = $_POST['period_id'];

if(!$period_id){
  echo json_encode(['status' => 'error', 'message' => 'period id not found.']);
  exit;
}

$stmt = $pdo->prepare("
  DELETE FROM evaluation_periods
  WHERE period_id = ?
");
$stmt->execute([$period_id]);

echo json_encode(['status' => 'success', 'message' => 'Evalution Period is successfully deleted.']);
exit;

?>