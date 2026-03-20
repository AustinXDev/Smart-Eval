<?php 
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../../vendor/autoload.php';

$input = json_decode(file_get_contents('php://input'), true);
$admin_username = trim($input['admin_username'] ?? '');
$IP = $_SERVER['REMOTE_ADDR'];

$limit = 3;
$blockHours = 5;

if (empty($admin_username)) {
    echo json_encode([
        'status'  => 'error',
        'message' => '❌ Username is required.'
    ]);
    exit;
}

//count attempts
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) AS attempt_count
    FROM admin_password_resets
    WHERE ip_address = ?
    AND created_at > NOW() - INTERVAL {$blockHours} HOUR
");
$stmtCount->execute([$IP]);
$attemptCount = $stmtCount->fetch()['attempt_count'] ?? 0;

if ($attemptCount >= $limit) {
    echo json_encode([
        'status'  => 'error',
        'message' => "Too many requests. Try again after 1 hour."
    ]);
    exit;
}

//fetch admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$admin_username]);
$admin = $stmt->fetch();

if($admin){
  $token = bin2hex(random_bytes(32));
  date_default_timezone_set('Asia/Manila');
  $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

  //insert attempts in database
  $stmt = $pdo->prepare("INSERT INTO admin_password_resets (admin_id, ip_address, token, expires_at) VALUES (?, ?, ?, ?)");
  $stmt->execute([$admin['admin_id'], $IP, $token, $expires]);

  $resetLink = "http://localhost/Smart-Eval/views/admin/reset_password.view.php?token=$token";

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
    $mail->Subject = 'SmartEval Admin Password Reset';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 400px; margin: auto;'>
            <h2 style='color: #4a0080;'>Password Reset Request</h2>
            <p>Hi <strong>{$admin['username']}</strong>,</p>
            <p>You requested a password reset. Click the button below:</p>
            <a href='{$resetLink}' 
               style='display: inline-block; background: #4a0080; color: white; 
                      padding: 12px 24px; border-radius: 8px; text-decoration: none;
                      font-weight: bold; margin: 16px 0;'>
                Reset Password
            </a>
            <p>This link expires in <strong>1 hour</strong>.</p>
            <p>Sent at: <strong>" . date('F j, Y h:i:s A') . " (Manila Time)</strong></p>
            <p style='color: red;'>If you didn't request this, please secure your account immediately.</p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        'status'  => 'success',
        'message' => 'If that username exists, a reset link has been sent to the associated email.'
    ]);
    exit;

} catch (Exception $e) {
    error_log("PHPMailer Error: " . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to send reset email. Please try again.'
    ]);
    exit;
}

} else {
  echo json_encode(['status'=>'error', 'message'=>'Admin not found!']);
}
?>