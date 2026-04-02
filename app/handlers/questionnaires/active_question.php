<?php
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$question_id = $_POST['question_id'] ?? '';

if (!$question_id) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid question.']);
  exit;
}

$stmt = $pdo->prepare("
  UPDATE questions 
  SET is_active = 1 
  WHERE question_id = ?
");
$stmt->execute([$question_id]);

echo json_encode([
  'status' => 'success',
  'message' => 'Question activated successfully.'
]);
?>