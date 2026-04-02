<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

function getQuestionSet($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM question_sets WHERE is_active = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $questionSets = getQuestionSet($pdo);
    echo json_encode(['status' => 'success', 'data' => $questionSets]);
} catch (PDOException $e) {
  echo json_encode(['status'=>'error', 'message'=>'Database error: '. $e->getMessage()]);
  exit;
}

?>