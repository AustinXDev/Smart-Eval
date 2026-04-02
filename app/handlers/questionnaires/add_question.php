<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$set_id = $_POST['set_id'] ?? '';
$question_name = $_POST['question_name'] ?? '';
$category = $_POST['categories'] ?? '';

if(!$set_id || !$question_name || !$category){
  echo json_encode(['status' => 'error', 'message' => 'Missing fields.']);
  exit;
}

try{

  $normalized = strtolower(
    preg_replace('/[^a-zA-Z0-9]/', '', $question_name)
  );

  //duplicate check
  $stmt = $pdo->prepare("
    SELECT question_id, is_active
    FROM questions
    WHERE set_id = ?
    AND LOWER(REPLACE(REPLACE(question_text, ' ', ''), '?', '')) = ?
    LIMIT 1
  ");
  $stmt->execute([$set_id, $normalized]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    if ($existing['is_active'] == 1) {
      throw new Exception("This question already exists.");
    } else {
      echo json_encode([
        'status' => 'warning',
        'question_id' => $existing['question_id'],
        'message' => 'This question exists but inactive. Activate it?'
      ]);
      exit;
    }
  }

  $pdo-> beginTransaction();

  //Insert Question
  $stmt = $pdo->prepare("
    INSERT INTO questions (set_id, question_text, category, is_active)
    VALUES (?, ?, ?, 1)
  ");
  $stmt->execute([$set_id, trim($question_name), $category]);

  $pdo->commit();

  echo json_encode([
      'status' => 'success',
      'message' => 'Question added successfully.'
  ]);
}
catch (Exception $e){

  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>