<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../config/database.php';

//Guard
if (!is2FAPending()) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$code    = trim($input['code'] ?? '');
$adminId = get2FAAdminId();

// Validate input
if (empty($code)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Verification code is required.'
    ]);
    exit;
}

if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Code must be exactly 6 digits.'
    ]);
    exit;
}

// Count failed 2FA attempts by IP
$IP          = $_SERVER['REMOTE_ADDR'];
$maxAttempts = 5;
$lockTime    = 10;

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) AS attempt_count
    FROM admin_2fa_attempts
    WHERE ip_address = ?
    AND attempted_at > (NOW() - INTERVAL {$lockTime} MINUTE)
    AND success = 0
");
$stmtCount->execute([$IP]);
$attemptCount = $stmtCount->fetch()['attempt_count'] ?? 0;

if ($attemptCount >= $maxAttempts) {
    echo json_encode([
        'status'  => 'error',
        'message' => "Too many failed attempts. Try again after {$lockTime} minutes."
    ]);
    exit;
}

// Fetch latest unused unexpired code
$stmt = $pdo->prepare("
    SELECT *, expires_at < NOW() AS is_expired
    FROM admin_2fa_codes
    WHERE admin_id = ?
    AND used = 0
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$adminId]);
$record = $stmt->fetch();

// Check if code exists
if (!$record) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'No verification code found. Please login again.'
    ]);
    exit;
}

// Check if code is expired
if ($record['is_expired']) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Code has expired. Please login again.'
    ]);
    exit;
}

// Verify code
if (hash_equals((string)$record['code'], (string)$code)) {

    // Mark code as used
    $pdo->prepare("
        UPDATE admin_2fa_codes 
        SET used = 1 
        WHERE id = ?
    ")->execute([$record['id']]);

    // Fetch admin details
    $stmtAdmin = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
    $stmtAdmin->execute([$adminId]);
    $admin = $stmtAdmin->fetch();

    session_regenerate_id(false);
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['is_admin'] = true;
    $_SESSION['role']     = $admin['role'];
    $_SESSION['username'] = $admin['username'];
    unset($_SESSION['2fa_pending'], $_SESSION['2fa_admin_id']);
    session_write_close();

    // Clear failed 2FA attempts
    $pdo->prepare("
        DELETE FROM admin_2fa_attempts 
        WHERE ip_address = ?
    ")->execute([$IP]);

    // Log successful attempt
    $pdo->prepare("
        INSERT INTO admin_2fa_attempts (admin_id, ip_address, success)
        VALUES (?, ?, 1)
    ")->execute([$adminId, $IP]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Verified successfully! Redirecting...'
    ]);
    exit;

} else {
    $remainingAttempts = max($maxAttempts - ($attemptCount + 1), 0);

    // Log failed attempt
    $pdo->prepare("
        INSERT INTO admin_2fa_attempts (admin_id, ip_address, success)
        VALUES (?, ?, 0)
    ")->execute([$adminId, $IP]);

    echo json_encode([
        'status'  => 'error',
        'message' => "Invalid code. {$remainingAttempts} attempt(s) remaining."
    ]);
    exit;
}
?>