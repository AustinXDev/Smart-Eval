<?php 

namespace App\Repositories\AdminRepo;

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

  /* ------------------------------------------------------------------
     Account Management (super admin only)
  ------------------------------------------------------------------ */

  /** @return Admin[] All administrator accounts, newest first. */
  public function findAll(): array
  {
    $stmt = $this->pdo->query("
      SELECT *
      FROM admins
      ORDER BY created_at DESC, admin_id DESC
    ");

    return array_map(
      static fn (array $row) => Admin::fromArray($row),
      $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
  }

  /** Username already used by another account (case-insensitive collation). */
  public function usernameExists(string $username, int $exceptId = 0): bool
  {
    $stmt = $this->pdo->prepare("
      SELECT 1 FROM admins WHERE username = ? AND admin_id <> ? LIMIT 1
    ");
    $stmt->execute([$username, $exceptId]);

    return (bool) $stmt->fetchColumn();
  }

  /** Email already used by another account (case-insensitive collation). */
  public function emailExists(string $email, int $exceptId = 0): bool
  {
    $stmt = $this->pdo->prepare("
      SELECT 1 FROM admins WHERE email = ? AND admin_id <> ? LIMIT 1
    ");
    $stmt->execute([$email, $exceptId]);

    return (bool) $stmt->fetchColumn();
  }

  /** Active super admins, optionally ignoring one account. */
  public function countActiveSuperAdmins(int $exceptId = 0): int
  {
    $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM admins
      WHERE role = ?
        AND status = ?
        AND admin_id <> ?
    ");
    $stmt->execute([Admin::ROLE_SUPER_ADMIN, Admin::STATUS_ACTIVE, $exceptId]);

    return (int) $stmt->fetchColumn();
  }

  /** @param array{username:string, full_name:string, email:string, role:string, status:string, password_hash:string} $data */
  public function create(array $data): int
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO admins (username, password_hash, full_name, email, role, status)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
      $data['username'],
      $data['password_hash'],
      $data['full_name'],
      $data['email'],
      $data['role'],
      $data['status'],
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  /** @param array{username:string, full_name:string, email:string, role:string, status:string} $data */
  public function updateAccount(int $adminId, array $data): void
  {
    $stmt = $this->pdo->prepare("
      UPDATE admins
      SET username = ?, full_name = ?, email = ?, role = ?, status = ?
      WHERE admin_id = ?
    ");
    $stmt->execute([
      $data['username'],
      $data['full_name'],
      $data['email'],
      $data['role'],
      $data['status'],
      $adminId,
    ]);
  }

  /**
   * Deletes an account and the rows that reference it. The 2FA tables use
   * ON DELETE RESTRICT, so they are cleared first; remembered devices are
   * removed so an old browser token can't be reused.
   */
  public function deleteAccount(int $adminId): bool
  {
    $this->pdo->beginTransaction();

    try {
      foreach (['admin_2fa_attempts', 'admin_2fa_codes', 'admin_2fa_resends'] as $table) {
        $this->pdo->prepare("DELETE FROM {$table} WHERE admin_id = ?")->execute([$adminId]);
      }

      $this->pdo->prepare("
        DELETE FROM trusted_devices WHERE account_type = 'admin' AND account_id = ?
      ")->execute([(string) $adminId]);

      $stmt = $this->pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
      $stmt->execute([$adminId]);
      $deleted = $stmt->rowCount() > 0;

      $this->pdo->commit();

      return $deleted;
    } catch (\Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
  }

  /** Stops remembered-device logins for an account (used when it is deactivated). */
  public function revokeTrustedDevices(int $adminId): void
  {
    $this->pdo->prepare("
      UPDATE trusted_devices
      SET revoked_at = NOW()
      WHERE account_type = 'admin' AND account_id = ? AND revoked_at IS NULL
    ")->execute([(string) $adminId]);
  }

  /* ---------- Account Management PIN ---------- */

  public function setPin(int $adminId, string $pinHash): void
  {
    $this->pdo->prepare("
      UPDATE admins
      SET access_pin_hash = ?, pin_failed_attempts = 0, pin_locked_until = NULL, updated_at = updated_at
      WHERE admin_id = ?
    ")->execute([$pinHash, $adminId]);
  }

  /** Records a wrong PIN; locks the PIN for $lockMinutes once $maxAttempts is reached. */
  public function recordPinFailure(int $adminId, int $maxAttempts, int $lockMinutes): void
  {
    $this->pdo->prepare("
      UPDATE admins
      SET pin_failed_attempts = pin_failed_attempts + 1,
          pin_locked_until = IF(pin_failed_attempts >= ?, NOW() + INTERVAL ? MINUTE, pin_locked_until),
          updated_at = updated_at
      WHERE admin_id = ?
    ")->execute([$maxAttempts, $lockMinutes, $adminId]);
  }

  public function clearPinFailures(int $adminId): void
  {
    $this->pdo->prepare("
      UPDATE admins SET pin_failed_attempts = 0, pin_locked_until = NULL, updated_at = updated_at WHERE admin_id = ?
    ")->execute([$adminId]);
  }

  /** Seconds left on a PIN lock (0 if not locked); clears an expired lock. */
  public function pinLockSecondsLeft(int $adminId): int
  {
    $stmt = $this->pdo->prepare("
      SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), pin_locked_until))
      FROM admins WHERE admin_id = ?
    ");
    $stmt->execute([$adminId]);
    $left = (int) $stmt->fetchColumn();

    if ($left === 0) {
      $this->pdo->prepare("
        UPDATE admins SET pin_failed_attempts = 0, pin_locked_until = NULL, updated_at = updated_at
        WHERE admin_id = ? AND pin_locked_until IS NOT NULL AND pin_locked_until <= NOW()
      ")->execute([$adminId]);
    }

    return $left;
  }

}

?>
