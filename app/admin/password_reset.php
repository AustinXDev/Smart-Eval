<?php 

header('Content-Type: application/json'); // ensures JSON for fetch
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? '';
$token = trim($input['token'] ?? '');

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM admin_password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
$stmt->execute([$token]);
$resetRequest = $stmt->fetch();

if(!$resetRequest){
  echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token!']);
  exit;
}

$updatePassword = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE admin_id = ?");
$updatePassword->execute([$hash, $resetRequest['admin_id']]);

$stmtMarkUsed = $pdo->prepare("UPDATE admin_password_resets SET used = 1 WHERE token = ?");
$stmtMarkUsed->execute([$token]);

echo json_encode(['status' => 'success', 'message' => 'Password has been reset successfully!']);
exit;

?>