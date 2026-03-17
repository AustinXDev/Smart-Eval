<?php 
session_start();
require_once '../config/database.php'; // now always returns JSON if DB fails

$input = json_decode(file_get_contents('php://input'), true);
$student_id = $input['student_id'] ?? '';
$password = $input['password'] ?? '';
$IP = $_SERVER['REMOTE_ADDR']; // get client IP address

$maxAttempts = 3;
$lockTime = 1; 

// Count failed login attempts
$stmtCountAttempts = $pdo->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts WHERE student_id = ? AND ip_address = ? AND failed_at > (NOW() - INTERVAL ? MINUTE)");
$stmtCountAttempts->execute([$student_id, $IP, $lockTime]);
$attemptData = $stmtCountAttempts->fetch();
$currentAttemptCount = $attemptData['attempt_count'];

// Calculate remaining attempts
$remainingAttempts = $maxAttempts - $currentAttemptCount;

if($attemptData['attempt_count'] >= $maxAttempts){
  echo json_encode(['status' => 'error', 'message' => "Too many failed login attempts. Try again after {$lockTime} minutes."]);
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch();

if($user && !empty($user['password_hash'])){
  if(password_verify($password, $user['password_hash'])){
    //correct password, set session and return success
    $_SESSION['student_id'] = $user['student_id'];

    //clear failed attempts on successful login
    $stmtClearAttempts = $pdo->prepare("DELETE FROM login_attempts WHERE student_id = ? AND ip_address = ?");
    $stmtClearAttempts->execute([$student_id, $IP]);

    echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
    exit;
  } else {

    $stmtStoreAttempts = $pdo->prepare("INSERT INTO login_attempts (student_id, ip_address) VALUES (?, ?)");
    $stmtStoreAttempts->execute([$student_id, $IP]);
    
    echo json_encode(['status' => 'error', 'message' => "❌ Incorrect password. {$remainingAttempts} attempt(s) remaining."]);
    exit;
  }
} else {

  $stmtStoreAttempts = $pdo->prepare("INSERT INTO login_attempts (student_id, ip_address) VALUES (?, ?)");
  $stmtStoreAttempts->execute([$student_id, $IP]);

  echo json_encode(['status' => 'error','message' => 'Invalid Student ID or password!']);
  exit;
}
?>