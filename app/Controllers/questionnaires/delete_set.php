<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$set_id = $_POST['set_id'];

if(empty($set_id)){
  echo json_encode(['status' => 'error', 'message' => 'Missing field.']);
  exit;
}

try{
  $pdo->beginTransaction();

  //check if it exist
  $stmt = $pdo->prepare("SELECT set_id FROM question_sets WHERE set_id = ?");
  $stmt->execute([$set_id]);
  if (!$stmt->fetch()) {
      throw new Exception("Set not found.");
  }

  //check if have active evaluation
  $stmt = $pdo->prepare("
      SELECT COUNT(*) 
      FROM evaluation_periods 
      WHERE set_id = ? 
      AND is_active = 1
  ");
  $stmt->execute([$set_id]);
  $is_active = $stmt->fetchColumn();

  if ($is_active > 0) {
      throw new Exception("This set is currently used in an active evaluation and cannot be deleted.");
  }

  //Check if there are answers
  $stmt = $pdo->prepare("
      SELECT COUNT(*) 
      FROM evaluation_answers ea
      JOIN questions q ON ea.question_id = q.question_id
      WHERE q.set_id = ?
  ");
  $stmt->execute([$set_id]);
  $answer_count = $stmt->fetchColumn();

  if($answer_count > 0) {

    //soft delete
    $stmt = $pdo->prepare("UPDATE question_sets SET is_active = 0 WHERE set_id = ?");
    $stmt->execute([$set_id]);

    $pdo->commit();
    echo json_encode([
        'status' => 'warning',
        'message' => "Some questions already have answers. The set was archived instead."
    ]);
    exit;
  }

  //Delete all questions
  $stmt = $pdo->prepare("DELETE FROM questions WHERE set_id = ?");
  $stmt->execute([$set_id]);

  //Delete the set
  $stmt = $pdo->prepare("DELETE FROM question_sets WHERE set_id = ?");
  $stmt->execute([$set_id]);

  $pdo->commit();

  echo json_encode([
    'status' => 'success',
    'message' => 'Set and all its questions deleted successfully.'
  ]);

}
catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>