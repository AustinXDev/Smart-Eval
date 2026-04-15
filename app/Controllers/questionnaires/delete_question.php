<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$question_id = $_POST['question_id'];

if(empty($question_id)){
  echo json_encode(['status' => 'error', 'message' => 'Question id is missing.']);
  exit;
}

try{
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT set_id, is_active FROM questions WHERE question_id = ?");
  $stmt->execute([$question_id]);
  $question = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$question) {
      throw new Exception("Question not found.");
  }

  $set_id = $question['set_id'];


  //check if there are student answer linked
  $stmt = $pdo->prepare("SELECT COUNT(*) as answer_count FROM evaluation_answers WHERE question_id = ?");
  $stmt->execute([$question_id]);
  $answer_count = $stmt->fetch(PDO::FETCH_ASSOC)['answer_count'];

  if ($answer_count > 0) {
    // Soft delete instead of hard delete
    $stmt = $pdo->prepare("UPDATE questions SET is_active = 0 WHERE question_id = ?");
    $stmt->execute([$question_id]);
    $pdo->commit();
    echo json_encode([
        'status' => 'warning',
        'message' => "Question has $answer_count student answers. It was deactivated instead of deleted."
    ]);
    exit;
  }

  //check minimum number of question set
  $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM questions WHERE set_id = ? AND is_active = 1");
  $stmt->execute([$set_id]);
  $active_questions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  $MIN_REQUIRED = 1; // set minimum
  if ($active_questions <= $MIN_REQUIRED) {
      throw new Exception("Cannot delete. Minimum $MIN_REQUIRED active question(s) required in this set.");
  }


  //hard  delete if pass in every condition
  $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = ?");
  $stmt->execute([$question_id]);

  $pdo->commit();
  echo json_encode(['status' => 'success', 'message' => 'Question deleted successfully.']);

} catch (Exception $e) {
  $pdo->rollBack(); 
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>