<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$teacher_id = $_POST['teacher_id'] ?? '';

if (!$teacher_id) {
  echo json_encode(['status' => 'error', 'message' => 'Teacher ID is missing.']);
  exit;
}

try {
  $pdo->beginTransaction();

  // Check if teacher exists
  $stmt = $pdo->prepare("SELECT teacher_id, is_active FROM teachers WHERE teacher_id = ?");
  $stmt->execute([$teacher_id]);
  $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$teacher) {
    throw new Exception("Teacher not found.");
  }

  //Prevent action if already inactive
  if ($teacher['is_active'] == 0) {
    throw new Exception("This teacher is already deactivated.");
  }

  //Check if teacher is in ACTIVE evaluation
  $stmt = $pdo->prepare("
    SELECT 1
    FROM teacher_load tl
    JOIN evaluation_periods ep 
      ON ep.is_active = 1
     AND ep.target_dept = (
        SELECT department FROM teachers WHERE teacher_id = tl.teacher_id
     )
    WHERE tl.teacher_id = ?
    LIMIT 1
  ");
  $stmt->execute([$teacher_id]);

  if ($stmt->fetch()) {
    throw new Exception("Cannot delete: Teacher is currently part of an active evaluation.");
  }

  //Check if teacher has ANY feedback/history
  $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM evaluation_status es
    JOIN teacher_load tl ON es.load_id = tl.load_id
    WHERE tl.teacher_id = ?
  ");
  $stmt->execute([$teacher_id]);
  $feedback_count = $stmt->fetchColumn();

  //If has feedback → SOFT DELETE
  if ($feedback_count > 0) {

    $stmt = $pdo->prepare("UPDATE teachers SET is_active = 0 WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);

    $pdo->commit();

    echo json_encode([
      'status' => 'warning',
      'message' => "Teacher has $feedback_count evaluation records. Deactivated instead of deleted."
    ]);
    exit;
  }

  //If NO feedback → HARD DELETE
  $stmt = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = ?");
  $stmt->execute([$teacher_id]);

  $pdo->commit();

  echo json_encode([
    'status' => 'success',
    'message' => 'Teacher deleted successfully.'
  ]);

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);
}
?>