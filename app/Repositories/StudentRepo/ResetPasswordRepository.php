<?php 

namespace App\Repositories\StudentRepo;

use PDO;

class ResetPasswordRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  public function findValidToken(string $tokenHash): ?array
  {

    $stmt = $this->pdo->prepare("
      SELECT *
      FROM password_resets
      WHERE token = ?
        AND expires_at > NOW()
        AND used = 0
      LIMIT 1
    ");
    
    $stmt->execute([$tokenHash]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  }

  public function markAsUsed(string $token): bool
  {

    $stmt = $this->pdo->prepare("
      UPDATE password_resets
      SET used = 1
      WHERE token = ?
    ");

    $stmt->execute([$token]);

    return $stmt->rowCount() > 0;

  }

}

?>