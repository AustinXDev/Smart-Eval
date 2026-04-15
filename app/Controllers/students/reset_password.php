<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing student ID.']);
    exit;
}

// Reset password to default (e.g., "password123")
$default_password = 'password123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

//check if student exists and registered password
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$student){
  echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
  exit;
}

if(!$student['password_hash']){
  echo json_encode(['status' => 'error', 'message' => 'Student does not have a registered password.']);
  exit;
}

$stmt = $pdo->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
$stmt->execute([$hashed_password, $student_id]);

echo json_encode(['status' => 'success', 'message' => 'Password has been reset to default.']);
exit;
?>