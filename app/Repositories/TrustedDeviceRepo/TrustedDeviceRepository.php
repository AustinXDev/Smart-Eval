<?php

declare(strict_types=1);

namespace App\Repositories\TrustedDeviceRepo;

use PDO;
use PDOException;
use RuntimeException;

final class TrustedDeviceRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findBySelector(
        string $selector,
        string $accountType,
        string $accountId
    ): ?array {

        $stmt = $this->pdo->prepare("
      SELECT *
      FROM trusted_devices
      WHERE selector = ?
        AND account_type = ?
        AND account_id = ?
        AND revoked_at IS NULL
        AND expires_at > NOW()
      LIMIT 1
    ");

        $stmt->execute([
          $selector,
          $accountType,
          $accountId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;

    }

    public function create(
        string $accountType,
        string $accountId,
        string $selector,
        string $tokenHash,
        string $deviceName,
        string $expiresAt
    ): int {

        $stmt = $this->pdo->prepare("
        INSERT INTO trusted_devices (
          account_type,
          account_id,
          selector,
          token_hash,
          device_name,
          expires_at
        )
        VALUES (
          ?,
          ?,
          ?,
          ?,
          ?,
          ?
        )
      ");

        $stmt->execute([
          $accountType,
          $accountId,
          $selector,
          $tokenHash,
          $deviceName,
          $expiresAt
        ]);

        return (int) $this->pdo->lastInsertId();

    }

    public function getActiveDevices(
        string $accountType,
        string $accountId
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT
              device_id,
              selector,
              device_name,
              created_at,
              last_used_at,
              expires_at
          FROM trusted_devices
          WHERE account_type = ?
            AND account_id = ?
            AND revoked_at IS NULL
            AND expires_at > NOW()
          ORDER BY created_at DESC, device_id DESC
        ");

        $stmt->execute([
          $accountType,
          $accountId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function countActiveDevices(
        string $accountType,
        string $accountId
    ): int {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM trusted_devices
            WHERE account_type = :account_type
              AND account_id = :account_id
              AND revoked_at IS NULL
              AND expires_at > NOW()
        ");

        $stmt->execute([
            'account_type' => $accountType,
            'account_id' => $accountId
        ]);

        return (int) $stmt->fetchColumn();

    }

    public function updateLastUsed(
        int $deviceId
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE trusted_devices
            SET last_used_at = NOW()
            WHERE device_id = ?
              AND revoked_at IS NULL
              AND expires_at > NOW()
        ");

        $stmt->execute([
            $deviceId
        ]);
    }

    public function revoke(
        int $deviceId,
        string $accountType,
        string $accountId
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE trusted_devices
            SET revoked_at = NOW()
            WHERE device_id = ?
              AND account_type = ?
              AND account_id = ?
              AND revoked_at IS NULL
        ");

        $stmt->execute([
            $deviceId,
            $accountType,
            $accountId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function revokeAll(
        string $accountType,
        string $accountId
    ): int {
        $stmt = $this->pdo->prepare("
            UPDATE trusted_devices
            SET revoked_at = NOW()
            WHERE account_type = ?
              AND account_id = ?
              AND revoked_at IS NULL
        ");

        $stmt->execute([
            $accountType,
            $accountId
        ]);

        return $stmt->rowCount();
    }

    public function revokeBySelector(
        string $selector,
        string $accountType,
        string $accountId
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE trusted_devices
            SET revoked_at = NOW()
            WHERE selector = ?
              AND account_type = ?
              AND account_id = ?
              AND revoked_at IS NULL
        ");

        $stmt->execute([
            $selector,
            $accountType,
            $accountId
        ]);
    }

    public function revokeOldest(
        string $accountType,
        string $accountId
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE trusted_devices
            SET revoked_at = NOW()
            WHERE device_id = (
                SELECT device_id
                FROM (
                    SELECT device_id
                    FROM trusted_devices
                    WHERE account_type = ?
                      AND account_id = ?
                      AND revoked_at IS NULL
                      AND expires_at > NOW()
                    ORDER BY created_at ASC, device_id ASC
                    LIMIT 1
                ) AS oldest
            )
        ");

        $stmt->execute([
            $accountType,
            $accountId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteExpired(
        int $limit = 1000
    ): int {
        $limit = max(1, min($limit, 10000));

        $stmt = $this->pdo->prepare("
            DELETE FROM trusted_devices
            WHERE expires_at <= NOW()
               OR (
                   revoked_at IS NOT NULL
                   AND revoked_at < DATE_SUB(
                       NOW(),
                       INTERVAL 7 DAY
                   )
               )
            LIMIT {$limit}
        ");

        $stmt->execute();

        return $stmt->rowCount();
    }

}
