<?php 

namespace App\Repositories; 

use PDO;

class TwoFactorRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  public function createCode(
    string $studentId,
    string $codeHash,
    string $purpose,
    string $expiresAt
  ): bool {

    $stmt = $this->pdo->prepare("
      INSERT INTO 2fa_codes (
        student_id,
        code_hash,
        purpose,
        expires_at
      )
      VALUES(?, ?, ?, ?)
    ");

    return $stmt->execute([
      $studentId,
      $codeHash,
      $purpose,
      $expiresAt
    ]);

  }

  public function findValidCode(
    string $studentId,
  ): ?array
  {

    $stmt = $this->pdo->prepare("
      SELECT *
      FROM 2fa_codes
      WHERE student_id = ?
        AND used = 0
        AND expires_at > NOW()
      ORDER BY created_at DESC
      LIMIT 1
    ");

    $stmt->execute([
      $studentId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  }

  public function markAsUsed(
    int $id
  ): bool
  {

    $stmt = $this->pdo->prepare("
      UPDATE 2fa_codes
      SET used = 1
      WHERE id = ?
        AND used = 0
    ");

    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;

  }

  public function deletePreviousCodes(
    string $studentId,
    string $purpose
  ): void {

    $stmt = $this->pdo->prepare("
      UPDATE 2fa_codes
      SET used = 1
      WHERE student_id = ?
        AND purpose = ?
        AND used = 0
    ");

    $stmt->execute([
      $studentId,
      $purpose
    ]);

  }

  public function recordAttempt(
    ?string $studentId,
    string $purpose,
    string $ip,
    bool $success
  ): void {

    $stmt = $this->pdo->prepare("
      INSERT INTO 2fa_attempts (
        student_id,
        purpose,
        ip_address,
        success
      )
      VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
      $studentId,
      $purpose,
      $ip,
      $success ? 1 : 0
    ]);

  }

  public function countRecentFailures(
    string $studentId,
    string $purpose,
    int $withinMinutes,
  ): int {

    $stmt = $this->pdo->prepare("
      SELECT COUNT(*) AS attempt_count
      FROM 2fa_attempts
      WHERE student_id = ?
        AND purpose = ?
        AND success = 0
        AND attempted_at > DATE_SUB(
          NOW(),
          INTERVAL ? MINUTE
        )
    ");

    $stmt->execute([
      $studentId, 
      $purpose,
      $withinMinutes
    ]);

    return (int) (
      $stmt->fetch(PDO::FETCH_ASSOC)['attempt_count'] ?? 0
    );

  }

  public function hasRecentCode(
    string $studentId,
    string $purpose,
    int $seconds = 60
  ): bool {

    $stmt = $this->pdo->prepare("
      SELECT id
      FROM 2fa_codes
      WHERE student_id = ?
        AND purpose = ?
        AND created_at > DATE_SUB(
          NOW(),
          INTERVAL ? SECOND
        )
      LIMIT 1
    ");

    $stmt->execute([
      $studentId,
      $purpose,
      $seconds
    ]);


    return $stmt->fetchColumn() !== false;  
  }

}

?>