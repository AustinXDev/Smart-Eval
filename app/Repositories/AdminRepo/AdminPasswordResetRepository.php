<?php 

namespace App\Repositories\AdminRepo;

use PDO;

class AdminPasswordResetRepository
{

public function __construct(
  private PDO $pdo
)
{
}

public function countRecentAttempts(
  int $adminId,
  string $ip,
  string $blockHours
): int {

  $cutoff = (new \DateTimeImmutable(
      'now',
      new \DateTimeZone('Asia/Manila')
  ))
      ->modify("-{$blockHours} hours")
      ->format('Y-m-d H:i:s');

  $stmt = $this->pdo->prepare("
    SELECT COUNT(*)
    FROM admin_password_resets
    WHERE admin_id = ?
      AND ip_address = ?
      AND created_at > ?
  ");

  $stmt->execute([
    $adminId,
    $ip,
    $cutoff
  ]);

  return $stmt->fetchColumn();
}

public function create(
  int $adminId,
  string $ip,
  string $token,
  string $expiresAt
): bool {

  $stmt = $this->pdo->prepare("
    INSERT INTO admin_password_resets
      (
        admin_id,
        ip_address,
        token,
        expires_at
      )
    VALUES
      (?, ?, ?, ?)
  ");

  return $stmt->execute([
    $adminId,
    $ip,
    $token,
    $expiresAt
  ]);

}


}

?>