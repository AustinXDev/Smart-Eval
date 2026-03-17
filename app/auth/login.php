<?php 
session_start();
require_once '../config/database.php'; // now always returns JSON if DB fails

$input = json_decode(file_get_contents('php://input'), true);
$student_id = $input['student_id'] ?? '';
$password = $input['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND password = ?");
$stmt->execute([$student_id, $password]);
$user = $stmt->fetch();

if($user){
  $_SESSION['student_id'] = $user['student_id'];
  echo json_encode(['status'=>'success','message'=>'Login successful!']);
} else {
  echo json_encode(['status'=>'error','message'=>'Invalid Student ID or password!']);
}
?>