<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../../vendor/autoload.php';

if(!is2FAPending()){
  echo json_encode([
    'status' => 'error',
    'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

$adminId = get2FAAdminId();
$IP = $_SERVER['REMOTE_ADDR'];

$maxResends = 3;
$lockTime = 1;

//count resend attempt by IP
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) AS resend_count
    FROM admin_2fa_resends
    WHERE ip_address = ?
    AND requested_at > (NOW() - INTERVAL {$lockTime} MINUTE)
");
$stmtCount->execute([$IP]);
$resendCount = $stmtCount->fetch()['resend_count'] ?? 0;

//Block if limit reached
if ($resendCount >= $maxResends) {
    echo json_encode([
        'status'  => 'error',
        'message' => "Too many resend attempts. Try again after {$lockTime} minutes."
    ]);
    exit;
}

//Fetch admin details
$stmtAdmin = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmtAdmin->execute([$adminId]);
$admin = $stmtAdmin->fetch();

if (!$admin) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Admin not found. Please login again.'
    ]);
    exit;
}

//delete unused  old request
$pdo->prepare("
    DELETE FROM admin_2fa_codes 
    WHERE admin_id = ? AND used = 0
")->execute([$adminId]);

//generate new code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

//Insert new code
$pdo->prepare("
    INSERT INTO admin_2fa_codes (admin_id, code, expires_at) 
    VALUES (?, ?, NOW() + INTERVAL 10 MINUTE)
")->execute([$adminId, $code]);

//update expiration date
$_SESSION['2fa_expires_at'] = time() + (10 * 60);

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
    $mail->Subject = 'Your New SmartEval Verification Code';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 400px; margin: auto;'>
            <h2 style='color: #4a0080;'>SmartEval Verification</h2>
            <p>Hi <strong>{$admin['username']}</strong>,</p>
            <p>You requested a new verification code. Your new code is:</p>
            <h1 style='letter-spacing: 12px; color: #4a0080;'>{$code}</h1>
            <p>This code expires in <strong>10 minutes</strong>.</p>
            <p style='color: red;'>If you didn't request this, please secure your account immediately.</p>
        </div>
    ";

    $mail->send();

    // Log resend attempt
    $pdo->prepare("
        INSERT INTO admin_2fa_resends (admin_id, ip_address)
        VALUES (?, ?)
    ")->execute([$adminId, $IP]);

    $remainingResends = max($maxResends - ($resendCount + 1), 0);

    echo json_encode([
        'status'  => 'success',
        'message' => "New verification code sent! {$remainingResends} resend(s) remaining."
    ]);
    exit;
} catch(Exception $e) {
    error_log("PHPMailer Resend Error: " . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to send verification code. Please try again.'
    ]);
    exit;
}
?>