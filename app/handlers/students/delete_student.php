<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$student_id = trim($_POST['student_id'] ?? '');
$force = $_POST['force'] ?? 0;

if(empty($student_id)){
  echo json_encode(['status' => 'error', 'message' => 'Student ID is required.']);
  exit;
}

try {

  //check if student exists
  $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
  $stmt->execute([$student_id]);

  if(!$stmt->fetch()){
    echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
    exit;
  }

  // Check evaluation records
  $check = $pdo->prepare("SELECT COUNT(*) FROM evaluation_status WHERE student_id = ?");
  $check->execute([$student_id]);
  $count = $check->fetchColumn();

  // if no evaluation record -> hard delete
  if ($count == 0) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);

    echo json_encode([
      'status' => 'success',
      'message' => 'Student permanently deleted.'
    ]);
    exit;
  }

  // If has evaluation and NOT forced → send warning
  if ($count > 0 && !$force) {
    echo json_encode([
      'status' => 'warning',
      'message' => "This student has $count evaluation record(s).",
      'requiresConfirm' => true
    ]);
    exit;
  }

  //soft delete student
  $stmt = $pdo->prepare("UPDATE students SET is_active = 0 WHERE student_id = ?");
  $stmt->execute([$student_id]);

  echo json_encode([
      'status' => 'success',
      'message' => 'Student has been set to inactive.'
  ]);

} catch (Exception $e){
  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage() // 👈 show real error
  ]);
}

?>