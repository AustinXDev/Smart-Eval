<?php 

namespace App\Repositories\StudentRepo;

use PDO;

class PasswordResetRepository
{

  public function __construct(
    private PDO $pdo
  ){
  }

  public function cleanupExpired(): void
  {

    $this->pdo->prepare("
      DELETE FROM password_resets
      WHERE created_at < NOW() - INTERVAL 1 DAY
    ");

  }

  public function countRecentAttempts(
    string $studentId,
    string $ip,
    int $hours
  ): int {

    $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM password_resets
      WHERE student_id = ?
        AND ip_address = ?
        AND created_at > NOW() - INTERVAL {$hours} HOUR
    ");

    $stmt->execute([$studentId, $ip]);

    return (int)$stmt->fetchColumn();

  }

  public function create(
    string $studentId,
    string $ip,
    string $tokenHash,
    string $expires
  ): void {

    $stmt = $this->pdo->prepare("
      INSERT INTO password_resets
      (
        student_id,
        ip_address, 
        token,
        expires_at,
        used
      )
      VALUES(?, ?, ?, ?, 0)
    ");

    $stmt->execute([
      $studentId,
      $ip,
      $tokenHash,
      $expires,
    ]);
  }
}

?>