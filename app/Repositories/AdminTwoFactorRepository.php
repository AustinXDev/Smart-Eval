<?php 

namespace App\Repositories;

use PDO;

class AdminTwoFactorRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  public function deletePreviousCodes(
    int $adminId
  ): void {

     $stmt = $this->pdo->prepare("
          DELETE FROM admin_2fa_codes
          WHERE admin_id = ?
            AND used = 0
      ");

      $stmt->execute([$adminId]);

  }

  public function createCode(
    int $adminId,
    string $codeHash,
    string $expiresAt
  ): bool {

    $stmt = $this->pdo->prepare("
        INSERT INTO admin_2fa_codes
            (admin_id, code_hash, expires_at, used)
        VALUES
            (?, ?, ?, 0)
    ");

    return $stmt->execute([
        $adminId,
        $codeHash,
        $expiresAt
    ]);

  }

  public function findValidCode(
    int $adminId
  ): ?array {

      $stmt = $this->pdo->prepare("
          SELECT *
          FROM admin_2fa_codes
          WHERE admin_id = ?
            AND used = 0
            AND expires_at > NOW()
          ORDER BY id DESC
          LIMIT 1
      ");

      $stmt->execute([$adminId]);

      return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  }

  /**
   * Mark OTP code used
   */
  public function markAsUsed(
        int $id
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE admin_2fa_codes
            SET used = 1
            WHERE id = ?
              AND used = 0
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

  /**
   * Count failed OTP attempts
   */
  public function countRecentFailures(
    int $adminId,
    string $ip,
    int $minutes
  ): int
  {

  $cutoff = (new \DateTimeImmutable())
      ->modify("-{$minutes} minutes")
      ->format('Y-m-d H:i:s');

    $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM admin_2fa_attempts
      WHERE admin_id = ?
        AND ip_address = ?
        AND success = 0
        AND attempted_at > ?  
    ");

    $stmt->execute([
      $adminId,
      $ip,
      $cutoff
    ]);

    return (int) $stmt->fetchColumn();

  }


  /**
   * Record an OTP attempt
   */
  public function recordAttempt(
    int $adminId,
    string $ip,
    bool $success
  ): bool {

    $stmt = $this->pdo->prepare("
      INSERT INTO admin_2fa_attempts
        (
          admin_id,
          ip_address,
          success,
          attempted_at
        )
      VALUES
        (?, ?, ?, NOW())
    ");

    return $stmt->execute([
      $adminId,
      $ip,
      $success ? 1 : 0
    ]);
  }

  /**
   * Delete old attempt records
   */
  public function deleteOldAttempts(
    int $minutes
  ): void {

    $stmt = $this->pdo->prepare("
      DELETE FROM admin_2fa_attempts
      WHERE attempted_at < (
        NOW() - INTERVAL ? MINUTE
      )
    ");

    $stmt->execute([
      $minutes
    ]);

  }

}

?>