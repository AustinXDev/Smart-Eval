<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);  // ← hide errors from output
ini_set('log_errors', 1);
header('Content-Type: application/json');
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$student_id = trim($input['student_id'] ?? '');
$password = trim($input['password'] ?? '');
$IP = $_SERVER['REMOTE_ADDR'];

$maxAttempts = 3;
$lockTime = 1;

// Validate input
if (empty($student_id) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '❌ Student ID and password are required.']);
    exit;
}

// Count failed login attempts
$stmtCountAttempts = $pdo->prepare("
    SELECT COUNT(*) as attempt_count 
    FROM login_attempts 
    WHERE ip_address = ? 
    AND failed_at > (NOW() - INTERVAL ? MINUTE)
    AND success = 0
");
$stmtCountAttempts->execute([$IP, $lockTime]);
$attemptData = $stmtCountAttempts->fetch();

// Check if locked out
if ($attemptData['attempt_count'] >= $maxAttempts) {
    echo json_encode([
        'status' => 'error',
        'message' => "Too many failed login attempts. Try again after {$lockTime} minute(s)."
    ]);
    exit;
}

// Calculate remaining attempts
$remainingAttempts = max($maxAttempts - ($attemptData['attempt_count'] + 1), 0);

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch();

if ($user && !empty($user['password_hash'])) {
    if (password_verify($password, $user['password_hash'])) {

        // Successful login
        session_regenerate_id(true);
        $_SESSION['student_id'] = $user['student_id'];

        // Clear failed attempts
        $stmtClearAttempts = $pdo->prepare("
            DELETE FROM login_attempts 
            WHERE ip_address = ?
        ");
        $stmtClearAttempts->execute([$IP]);

        // Log successful attempt
        $stmtLog = $pdo->prepare("
            INSERT INTO login_attempts (student_id, ip_address, success) 
            VALUES (?, ?, 1)
        ");
        $stmtLog->execute([$student_id, $IP]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful!'
        ]);
        exit;

    } else {
        // Wrong password
        $stmtStoreAttempts = $pdo->prepare("
            INSERT INTO login_attempts (student_id, ip_address, success) 
            VALUES (?, ?, 0)
        ");
        $stmtStoreAttempts->execute([$student_id, $IP]);

        echo json_encode([
            'status' => 'error',
            'message' => "Incorrect student ID or password. {$remainingAttempts} attempt(s) remaining."
        ]);
        exit;
    }
} else {
    // Student not found
    $stmtStoreAttempts = $pdo->prepare("
        INSERT INTO login_attempts (student_id, ip_address, success) 
        VALUES (?, ?, 0)
    ");
    $stmtStoreAttempts->execute([$student_id, $IP]);

    echo json_encode([
        'status' => 'error',
        'message' => "Incorrect student ID or password. {$remainingAttempts} attempt(s) remaining."
    ]);
    exit;
}
?>