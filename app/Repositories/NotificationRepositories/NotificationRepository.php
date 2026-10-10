<?php


declare(strict_types=1);

namespace App\Repositories\NotificationRepositories;

use PDO;
use Throwable;

final class NotificationRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    /**
     * Queue one reminder per student and evaluation period.
     * Existing unique keys prevent duplicate reminders.
     */
    public function enqueue(
        int $periodId,
        string $studentId,
        string $type = 'evaluation_reminder'
    ): bool {

        $stmt = $this->pdo->prepare("
        INSERT INTO notification_queue
          (period_id, student_id, type, channel, status, available_at)
        VALUES
          (?, ?, ?, 'email', 'pending', NOW())
        ON DUPLICATE KEY UPDATE
                notification_id = notification_id 
      ");

        return $stmt->execute([
          $periodId,
          $studentId,
          $type
        ]);

    }


    /**
     * Recover jobs stuck in processing after a worker crash.
     */
    public function recoverStaleJobs(
        int $leaseMinutes = 10
    ): int {
        // Keep the lease timeout within a safe range.
        $leaseMinutes = max(1, min($leaseMinutes, 60));

        $stmt = $this->pdo->prepare("
        UPDATE notification_queue
        SET
            status = CASE
                WHEN attempt_count >= 3 THEN 'failed'
                ELSE 'pending'
            END,
            available_at = CASE
                WHEN attempt_count >= 3 THEN available_at
                ELSE NOW()
            END,
            failed_at = CASE
                WHEN attempt_count >= 3 THEN NOW()
                ELSE failed_at
            END,
            error_message = 'Worker lease expired; job recovered.',
            processing_started_at = NULL
        WHERE status = 'processing'
          AND processing_started_at IS NOT NULL
          AND processing_started_at <
              DATE_SUB(NOW(), INTERVAL {$leaseMinutes} MINUTE)
    ");

        $stmt->execute();

        return $stmt->rowCount();
    }



    /**
     * Atomically claim a batch of pending jobs.
     * Compatible with MariaDB versions that do not support SKIP LOCKED.
     *
     * @return list<array<string, mixed>>
     */
    public function claimBatch(int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        $claimToken = bin2hex(random_bytes(16));

        try {
            $this->pdo->beginTransaction();

            // Claim available jobs and assign this worker's unique token.
            $stmt = $this->pdo->prepare("
            UPDATE notification_queue
            SET
                status = 'processing',
                claim_token = ?,
                processing_started_at = NOW(),
                attempt_count = attempt_count + 1
            WHERE status = 'pending'
              AND available_at <= NOW()
              AND attempt_count < 3
            ORDER BY available_at ASC, notification_id ASC
            LIMIT {$limit}
        ");

            $stmt->execute([$claimToken]);

            // Retrieve only jobs claimed by this worker.
            $select = $this->pdo->prepare("
            SELECT
                notification_id,
                period_id,
                student_id,
                type,
                channel,
                attempt_count
            FROM notification_queue
            WHERE claim_token = ?
              AND status = 'processing'
            ORDER BY notification_id ASC
        ");

            $select->execute([$claimToken]);

            $jobs = $select->fetchAll(PDO::FETCH_ASSOC);

            $this->pdo->commit();

            return $jobs;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    public function markSent(
        int $notification_id
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE notification_queue
            SET status = 'sent',
                sent_at = NOW(),
                failed_at = NULL,
                error_message = NULL,
                processing_started_at = NULL
            WHERE notification_id = ?
              AND status = 'processing'
        ");

        $stmt->execute([$notification_id]);

    }


    public function markFailed(
        int $notificationId,
        int $attemptCount,
        string $error
    ): void {
        $terminal = $attemptCount >= 3;

        // Exponential delay: 1 minute after attempt 1, 5 after attempt 2.
        $delayMinutes = $attemptCount === 1 ? 1 : 5;

        $sql = $terminal
            ? "UPDATE notification_queue
               SET status = 'failed',
                   failed_at = NOW(),
                   error_message = :error,
                   processing_started_at = NULL
               WHERE notification_id = :id
                 AND status = 'processing'"
            : "UPDATE notification_queue
               SET status = 'pending',
                   available_at = DATE_ADD(NOW(), INTERVAL {$delayMinutes} MINUTE),
                   error_message = :error,
                   processing_started_at = NULL
               WHERE notification_id = :id
                 AND status = 'processing'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'    => $notificationId,
            'error' => mb_substr($error, 0, 2000),
        ]);
    }

    public function markSkipped(int $notificationId): void
    {
        $stmt = $this->pdo->prepare("
        UPDATE notification_queue
        SET status = 'skipped',
            sent_at = NULL,
            failed_at = NULL,
            error_message = NULL,
            processing_started_at = NULL
        WHERE notification_id = ?
          AND status = 'processing'
    ");

        $stmt->execute([$notificationId]);
    }

}
