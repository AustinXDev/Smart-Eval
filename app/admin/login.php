<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../../vendor/autoload.php';

$input = json_decode(file_get_contents('php://input'), true);
$admin_username = trim($input['admin_username'] ?? '');
$admin_password = trim($input['admin_password'] ?? '');
$IP = $_SERVER['REMOTE_ADDR'];

$maxAttempts = 3;
$lockTime = 1;

// Validate input
if (empty($admin_username) || empty($admin_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
    exit;
}

// Count failed attempts by IP only
$stmtCountAttempts = $pdo->prepare("
    SELECT COUNT(*) AS attempt_count 
    FROM admin_login_attempts 
    WHERE ip_address = ? 
    AND attempt_time > (NOW() - INTERVAL {$lockTime} MINUTE)
    AND success = 0
");
$stmtCountAttempts->execute([$IP]);
$attemptCount = $stmtCountAttempts->fetch()['attempt_count'] ?? 0;

if ($attemptCount >= $maxAttempts) {
    echo json_encode([
        'status' => 'error',
        'message' => "Too many failed login attempts. Try again after {$lockTime} minute(s)."
    ]);
    exit;
}

// Fetch admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$admin_username]);
$admin = $stmt->fetch();

if ($admin && !empty($admin['password_hash'])) {
    if (password_verify($admin_password, $admin['password_hash'])) {

        // generate 2FA code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old unused codes for this admin
        $pdo->prepare("
            DELETE FROM admin_2fa_codes 
            WHERE admin_id = ? AND used = 0
        ")->execute([$admin['admin_id']]);

        // Save new code
        $pdo->prepare("
            INSERT INTO admin_2fa_codes (admin_id, code, expires_at) 
            VALUES (?, ?, NOW() + INTERVAL 10 MINUTE)
        ")->execute([$admin['admin_id'], $code]);

        // Send code via Gmail
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'smarteval02@gmail.com';
            $mail->Password   = 'jfzw tmgp imah iukq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->SMTPDebug  = 0;

            $mail->setFrom('smarteval02@gmail.com', 'SmartEval');
            $mail->addAddress($admin['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Your SmartEval Verification Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 400px; margin: auto;'>
                    <h2 style='color: #4a0080;'>SmartEval Verification</h2>
                    <p>Hi <strong>{$admin['username']}</strong>,</p>
                    <p>Your verification code is:</p>
                    <h1 style='letter-spacing: 12px; color: #4a0080;'>{$code}</h1>
                    <p>This code expires in <strong>10 minutes</strong>.</p>
                    <p style='color: red;'>If you didn't request this, please secure your account immediately.</p>
                </div>
            ";

            $mail->send();

            //Only set 2FA pending and expiration
            $_SESSION['2fa_pending']  = true;
            $_SESSION['2fa_admin_id'] = $admin['admin_id'];
            $_SESSION['2fa_expires_at'] = time() + (10 * 60); //10 minutes from now

            // Clear failed attempts
            $pdo->prepare("
                DELETE FROM admin_login_attempts 
                WHERE ip_address = ?
            ")->execute([$IP]);

            // Log successful credential attempt
            $pdo->prepare("
                INSERT INTO admin_login_attempts (admin_username, ip_address, success) 
                VALUES (?, ?, 1)
            ")->execute([$admin_username, $IP]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'A verification code has been sent to your email.'
            ]);
            exit;

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to send verification code. Please try again.'
            ]);
            exit;
        }

    } else {
        // Wrong password
        $remainingAttempts = max($maxAttempts - ($attemptCount + 1), 0);

        $pdo->prepare("
            INSERT INTO admin_login_attempts (admin_username, ip_address, success) 
            VALUES (?, ?, 0)
        ")->execute([$admin_username, $IP]);

        echo json_encode([
            'status'  => 'error',
            'message' => "Incorrect username or password. {$remainingAttempts} attempt(s) remaining."
        ]);
        exit;
    }
} else {
    // Admin not found
    $remainingAttempts = max($maxAttempts - ($attemptCount + 1), 0);

    $pdo->prepare("
        INSERT INTO admin_login_attempts (admin_username, ip_address, success) 
        VALUES (?, ?, 0)
    ")->execute([$admin_username, $IP]);

    echo json_encode([
        'status'  => 'error',
        'message' => "Incorrect username or password. {$remainingAttempts} attempt(s) remaining."
    ]);
    exit;
}
?>
