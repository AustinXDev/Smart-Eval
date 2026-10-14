<?php

declare(strict_types=1);

namespace App\Services\Email;

use PDO;
use RuntimeException;
use Throwable;

/** Rolling reservations shared by HTTP and cron; no transaction spans SMTP. */
final class ProviderQuota
{
    public function __construct(
        private PDO $pdo,
        private string $provider,
        private array $config
    ) {
    }

    /** Return zero on reservation, otherwise seconds until another attempt. */
    public function reserve(string $priority): int
    {
        if ($this->pdo->inTransaction()) {
            throw new RuntimeException('Email quota cannot run inside a business transaction.');
        }

        $daily = (int) ($this->config['MAIL_DAILY_LIMIT'] ?? 0);
        $minute = (int) ($this->config['MAIL_MINUTE_LIMIT'] ?? 0);
        $reserve = (int) ($this->config['MAIL_AUTH_RESERVE'] ?? 0);
        $pace = (int) ($this->config['MAIL_BULK_INTERVAL_SECONDS'] ?? 2);

        if (
            $daily < 1 ||
            $minute < 1 ||
            $reserve < 0 ||
            $reserve >= $daily ||
            $pace < 0
        ) {
            throw new RuntimeException('Invalid email quota configuration.');
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                '
                INSERT INTO email_provider_state (provider_key) 
                VALUES (?) 
                ON DUPLICATE KEY UPDATE 
                provider_key = provider_key'
            );
            $stmt->execute([$this->provider]);

            $stmt = $this->pdo->prepare(
                '
                SELECT 
                    UNIX_TIMESTAMP(NOW()) AS now_ts, 
                    UNIX_TIMESTAMP(cooldown_until) AS cooldown_ts, 
                    UNIX_TIMESTAMP(next_bulk_at) AS bulk_ts 
                FROM email_provider_state 
                WHERE provider_key = ? FOR UPDATE'
            );

            $stmt->execute([$this->provider]);

            $state = $stmt->fetch(PDO::FETCH_ASSOC);

            $now = (int) $state['now_ts'];

            $wait = max(0, (int) $state['cooldown_ts'] - $now);

            if ($priority === 'bulk') {
                $wait = max($wait, (int) $state['bulk_ts'] - $now);
            }

            $stmt = $this->pdo->prepare('
                SELECT 
                    COUNT(*) AS daily_count, 
                    COALESCE(SUM(reserved_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)), 0) AS minute_count 
                FROM email_provider_reservations 
                WHERE provider_key = ? 
                AND reserved_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
            ');

            $stmt->execute([$this->provider]);

            $usage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ((int) $usage['daily_count'] >= ($priority === 'bulk' ? $daily - $reserve : $daily)) {
                $wait = max($wait, 300);
            }

            if ((int) $usage['minute_count'] >= $minute) {
                $wait = max($wait, 60);
            }

            if ($wait === 0) {
                $stmt = $this->pdo->prepare('
                    INSERT INTO email_provider_reservations (provider_key, priority) 
                    VALUES (?, ?)
                ');

                $stmt->execute([$this->provider, $priority]);

                if ($priority === 'bulk') {

                    $stmt = $this->pdo->prepare("
                        UPDATE email_provider_state 
                        SET next_bulk_at = DATE_ADD(NOW(), INTERVAL {$pace} SECOND) 
                        WHERE provider_key = ?");
                    $stmt->execute([$this->provider]);
                }
            }
            $this->pdo->commit();
            return $wait;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function cooldown(int $seconds): void
    {
        $seconds = max(1, min($seconds, 86400));
        $stmt = $this->pdo->prepare("UPDATE email_provider_state SET cooldown_until = GREATEST(COALESCE(cooldown_until, NOW()), DATE_ADD(NOW(), INTERVAL {$seconds} SECOND)) WHERE provider_key = ?");
        $stmt->execute([$this->provider]);
    }
}
