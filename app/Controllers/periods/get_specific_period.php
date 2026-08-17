<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$periodId = $_GET['period_id'] ?? null;

if (!$periodId) {
  echo json_encode(['status' => 'error', 'message' => 'Period ID is required']);
  exit;
}

try {
  $period = getPeriodById($pdo, $periodId);

  if (!$period) {
    echo json_encode(['status' => 'error', 'message' => 'Period not found']);
    exit;
  }

  echo json_encode(['status' => 'success', 'period' => $period]);

} catch (PDOException $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Database error: ' . $e->getMessage()
  ]);
}

function getPeriodById($pdo, $periodId) {
  $stmt = $pdo->prepare("
    SELECT * 
    FROM evaluation_periods 
    WHERE period_id = :period_id
    LIMIT 1
  ");

  $stmt->bindParam(':period_id', $periodId, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>