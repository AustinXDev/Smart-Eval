<?php 
header('Content-Type: application/json'); // ensures JSON for fetch
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$studentId = $input['student_id'] ?? '';
$password = $input['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$user = $stmt->fetch();

//check if student ID not exists in the database
if(!$user){
  echo json_encode(['status' => 'error', 'message' => 'Student ID not exists']);
  exit;
}

//check if user already has a password set
if(!empty($user['password_hash'])){
  echo json_encode(['status' => 'error', 'message' => 'Student ID already registered']);
  exit;
}

//update password for the student
//password hashing
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$updateStmt = $pdo->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
$updateStmt->execute([$passwordHash, $studentId]);
echo json_encode(['status' => 'success', 'message' => 'Successfully registered!']);
exit;

?>