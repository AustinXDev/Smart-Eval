<?php 
// Load Composer's autoLoader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoLoader
require '../../vendor/autoload.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/redirect.php';

$input = json_decode(file_get_contents('php://input'), true);
$studentID = $input['studentID'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentID]);
$user = $stmt->fetch();

if($user){
  $token = bin2hex(random_bytes(32)); // generate a random token
  $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); //token expires in 1 hour

  //insert token into database
  $stmt = $pdo->prepare("INSERT INTO password_resets (student_id, token, expires_at) VALUES (?, ?, ?)");
  $stmt->execute([$user['student_id'], $token, $expires]);

  $resetLink = "http://localhost/SmartEval/views/auth/reset_password.view.php?token=$token";

  //send email with reset link
  $mail = new PHPMailer(true);

  try{
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'smarteval02@gmail.com';
    $mail->Password = 'ceor scrt hahc rwbn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    //Recipients
    $mail->setFrom('smarteval02@gmail.com', 'SmartEval');
    $mail->addAddress($user['email'], $user['student_name']);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "<p>Hi {$user['student_name']}</p>
    <p>You requested a password reset. Click below:</p>
    <a href='{$resetLink}'>Reset Password</a>
    <p>If you didn't request this, ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['status'=>'success', 'message'=>'Password reset link has been sent to your email!']);
  } catch (Exception $e){
    error_log("PHPMailer Error: " . $e->getMessage());
    error_log("SMTPDebug: " . $mail->ErrorInfo);
    echo json_encode(['status'=>'error', 'message'=>"SMTP Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage()]);
  }
}

else{
  echo json_encode(['status'=>'error', 'message'=>'Student ID not found!']);
}

?>