<?php 

namespace App\Repositories;

use App\Models\Admin;
use PDO;

class AdminRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  public function findByUsername(
    string $username
  ): ?Admin {
    
    $stmt = $this->pdo->prepare("
      SELECT *
      FROM admins
      WHERE username = ?
      LIMIT 1
    ");

    $stmt->execute([$username]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row
      ? Admin::fromArray($row)
      : null;

  }

  public function findById(
    int $adminId
  ): ?Admin {

    $stmt = $this->pdo->prepare("
      SELECT *
      FROM admins
      WHERE admin_id = ?
      LIMIT 1
    ");

    $stmt->execute([$adminId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row
      ?  Admin::fromArray($row)
      : null;

  }

  public function countRecentFailedAttempts(
      string $username,
      string $ip,
      int $minutes
  ): int {

      $stmt = $this->pdo->prepare("
          SELECT COUNT(*) AS attempt_count
          FROM admin_login_attempts
          WHERE ip_address = ?
            AND admin_username = ?
            AND attempt_time > (
                NOW() - INTERVAL ? MINUTE
            )
            AND success = 0
      ");

      $stmt->execute([
          $ip,
          $username,
          $minutes
      ]);

      return (int) (
          $stmt->fetchColumn() ?? 0
      );
  }


  public function recordLoginAttempt(
      string $username,
      string $ip,
      bool $success
  ): bool {

      $stmt = $this->pdo->prepare("
          INSERT INTO admin_login_attempts
              (
                  admin_username,
                  ip_address,
                  success
              )
          VALUES
              (?, ?, ?)
      ");

      return $stmt->execute([
          $username,
          $ip,
          $success ? 1 : 0
      ]);
  }


  public function clearFailedAttempts(
      string $username
  ): bool {

      $stmt = $this->pdo->prepare("
          DELETE FROM admin_login_attempts
          WHERE admin_username = ?
      ");

      return $stmt->execute([$username]);
  }

    /**
     * Update password.
     *
     * Used for password reset/change after
     * the account is already active.
     */
  public function updatePassword(
    int $adminId,
    string $passwordHash
  ): bool {

    $stmt = $this->pdo->prepare("
        UPDATE admins
        SET password_hash = ?
        WHERE admin_id = ?
    ");

    $stmt->execute([
        $passwordHash,
        $adminId
    ]);

    return $stmt->rowCount() > 0;

  }

}

?>