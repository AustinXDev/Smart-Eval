<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$admin_username = trim($input['admin_username'] ?? '');
$admin_password = trim($input['admin_password'] ?? '');
$IP = $_SERVER['REMOTE_ADDR'];

$maxAttempts = 3;
$lockTime = 1;

// Count failed attempts
$stmtCountAttempts = $pdo->prepare("
    SELECT COUNT(*) AS attempt_count 
    FROM admin_login_attempts 
    WHERE admin_username = ? AND ip_address = ? 
    AND attempt_time > (NOW() - INTERVAL ? MINUTE)
");
$stmtCountAttempts->execute([$admin_username, $IP, $lockTime]);
$attemptCount = $stmtCountAttempts->fetch()['attempt_count'] ?? 0;

if ($attemptCount >= $maxAttempts) {
    echo json_encode([
        "status" => "error", // ✅ was "success" — fix this too!
        "message" => "Too many failed login attempts. Try again after {$lockTime} minutes."
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$admin_username]);
$admin = $stmt->fetch();

if ($admin && !empty($admin['password_hash'])) {
    if (password_verify($admin_password, $admin['password_hash'])) {

        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['is_admin'] = true;
        $_SESSION['role'] = $admin['role'];


        //record success
        $stmtLog = $pdo->prepare("
            INSERT INTO admin_login_attempts (admin_username, ip_address, success)
            VALUES (?, ?, ?)
        ");
        $stmtLog->execute([$admin_username, $IP, 1]);

        //Delete attempts after success
        $stmtClearAttempts = $pdo->prepare("DELETE FROM admin_login_attempts WHERE admin_username = ? AND ip_address = ?");
        $stmtClearAttempts->execute([$admin_username, $IP]);

        echo json_encode([
            "status" => "success",
            "message" => "Logged in successfully!"
        ]);
        exit;

    } else {
        $remainingAttempts = max($maxAttempts - ($attemptCount + 1), 0);

        $stmtLog = $pdo->prepare("
            INSERT INTO admin_login_attempts (admin_username, ip_address, success)
            VALUES (?, ?, ?)
        ");
        $stmtLog->execute([$admin_username, $IP, 0]);

        echo json_encode([
            "status" => "error",
            "message" => "❌ Incorrect username or password. {$remainingAttempts} attempt(s) remaining."
        ]);
        exit;
    }
} else {
    $remainingAttempts = max($maxAttempts - ($attemptCount + 1), 0);

    $stmtLog = $pdo->prepare("
        INSERT INTO admin_login_attempts (admin_username, ip_address, success)
        VALUES (?, ?, ?)
    ");
    $stmtLog->execute([$admin_username, $IP, 0]);

    echo json_encode([
        "status" => "error",
        "message" => "❌ Incorrect username or password. {$remainingAttempts} attempt(s) remaining."
    ]);
    exit;
}
?>