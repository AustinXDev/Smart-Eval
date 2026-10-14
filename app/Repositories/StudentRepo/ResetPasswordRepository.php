<?php

namespace App\Repositories\StudentRepo;

use PDO;

class ResetPasswordRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function consumeAndReset(
        string $tokenHash,
        string $passwordHash
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $select = $this->pdo->prepare(
                "
                SELECT 
                    student_id 
                FROM password_resets 
                WHERE token = ? 
                AND used = 0 
                AND expires_at > NOW() 
                FOR UPDATE"
            );

            $select->execute([$tokenHash]);

            $record = $select->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->pdo->rollBack();
                return false;
            }

            $consume = $this->pdo->prepare(
                "
                UPDATE 
                    password_resets 
                SET used = 1 
                WHERE token = ? 
                AND used = 0 
                AND expires_at > NOW()"
            );

            $consume->execute([$tokenHash]);

            if ($consume->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $update = $this->pdo->prepare(
                "
                UPDATE students 
                SET password_hash = ? 
                WHERE student_id = ?"
            );

            $update->execute(
                [
                $passwordHash,
                $record['student_id']]
            );

            if ($update->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;

        }

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
