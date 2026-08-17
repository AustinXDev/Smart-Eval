<?php 

namespace App\Repositories;

use PDO;

class LoginAttemptRepository
{
  public function __construct(private PDO $pdo)
  {
  }

  public function countRecentFailures(
    string $studentId, 
    string $ip, 
    int $withinMinutes): int
  {
      $stmt = $this->pdo->prepare("
          SELECT COUNT(*) as attempt_count
          FROM login_attempts
          WHERE student_id = ?
            AND ip_address = ?
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)
            AND success = 0
      ");
      $stmt->execute([$studentId, $ip, $withinMinutes]);

      return (int)($stmt->fetch(PDO::FETCH_ASSOC)['attempt_count'] ?? 0);
  }

  public function recordFailure(?string $studentId, string $ip): void
  {
      $stmt = $this->pdo->prepare(
          "INSERT INTO login_attempts (student_id, ip_address, success) VALUES (?, ?, 0)"
      );
      $stmt->execute([$studentId, $ip]);
  }

  public function recordSuccess(string $studentId, string $ip): void
  {
      $stmt = $this->pdo->prepare(
          "INSERT INTO login_attempts (student_id, ip_address, success) VALUES (?, ?, 1)"
      );
      $stmt->execute([$studentId, $ip]);
  }

  public function clearForIp(string $studentId): void
  {
      $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE student_id = ?
      AND success = 0");
      $stmt->execute([$studentId]);
  }


}

?>