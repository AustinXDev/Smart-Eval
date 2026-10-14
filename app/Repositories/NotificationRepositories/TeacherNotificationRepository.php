<?php

namespace App\Repositories\NotificationRepositories;

use PDO;
use RuntimeException;

class TeacherNotificationRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    // Get the teacher information for a notification.
    public function getTeacher(int $teacherId): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT
            teacher_id,
            full_name,
            email,
            department,
            is_active
        FROM teachers
        WHERE teacher_id = ?
        LIMIT 1
    ");

        $stmt->execute([$teacherId]);

        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        return $teacher ?: null;
    }


    // Check if the teacher has responses included in the final report.
    public function hasReportableResponses(
        int $teacherId,
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT 1
        FROM evaluation_status es

        INNER JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id

        WHERE es.teacher_id = ?
          AND es.period_id = ?
          AND es.is_submitted = 1

          AND EXISTS (
              SELECT 1
              FROM evaluation_status completed
              WHERE completed.student_id = es.student_id
                AND completed.period_id = es.period_id
              GROUP BY completed.student_id
              HAVING COUNT(*) > 0
                 AND SUM(completed.is_submitted = 1) = COUNT(*)
          )

        LIMIT 1
    ");

        $stmt->execute([
            $teacherId,
            $periodId
        ]);

        return (bool) $stmt->fetchColumn();
    }


    // Retrieve pending teacher reports from closed periods.
    public function getPendingReports(
        int $limit = 10,
        int $maxAttempts = 3
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            q.queue_id,
            q.teacher_id,
            q.period_id,
            t.full_name,
            t.email,
            t.department,
            t.is_active

        FROM teacher_notification_queue q

        INNER JOIN teachers t
            ON t.teacher_id = q.teacher_id

        INNER JOIN evaluation_periods ep
            ON ep.period_id = q.period_id

        WHERE q.status = 'pending'
          AND ep.is_closed = 1
          AND ep.target_dept = t.department
          AND q.attempts < :max_attempts
          AND (
              q.next_attempt_at IS NULL
              OR q.next_attempt_at <= NOW()
          )

        ORDER BY q.created_at ASC, q.queue_id ASC

        LIMIT :batch_limit
    ");

        $stmt->bindValue(
            ':max_attempts',
            $maxAttempts,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':batch_limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Claim a pending report for the current worker.

    // Claim a pending report only when its evaluation period is closed
    // and the teacher belongs to the target department.
    public function claimReport(
        int $queueId,
        string $token,
        int $maxAttempts = 3
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE teacher_notification_queue
        SET status = 'processing',
            claimed_at = NOW(),
            claim_token = ?,
            attempts = attempts + 1
        WHERE queue_id = ?
          AND status = 'pending'
          AND attempts < ?
          AND (
              next_attempt_at IS NULL
              OR next_attempt_at <= NOW()
          )
          AND EXISTS (
              SELECT 1
              FROM evaluation_periods ep

              INNER JOIN teachers t
                  ON t.teacher_id =
                      teacher_notification_queue.teacher_id

              WHERE ep.period_id =
                  teacher_notification_queue.period_id
                AND ep.is_closed = 1
                AND ep.target_dept = t.department
          )
    ");

        $stmt->execute([
            $token,
            $queueId,
            $maxAttempts
        ]);

        return $stmt->rowCount() === 1;
    }




    // Mark a successfully submitted report as sent.
    public function markAsSent(
        int $queueId,
        string $token
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE teacher_notification_queue
            SET status = 'sent',
                processed_at = NOW(),
                last_error = NULL,
                next_attempt_at = NULL,
                claimed_at = NULL,
                claim_token = NULL
            WHERE queue_id = ?
              AND status = 'processing'
              AND claim_token = ?
        ");

        $stmt->execute([$queueId, $token]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                "Unable to mark queue {$queueId} as sent."
            );
        }
    }


    // Skip reports that are not eligible for distribution.
    public function markAsSkipped(
        int $queueId,
        string $token,
        string $reason = 'NO_EVALUATION_RESPONSES'
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE teacher_notification_queue
            SET status = 'skipped',
                last_error = ?,
                processed_at = NOW(),
                next_attempt_at = NULL,
                claimed_at = NULL,
                claim_token = NULL
            WHERE queue_id = ?
              AND status = 'processing'
              AND claim_token = ?
        ");

        $stmt->execute([
            $reason,
            $queueId,
            $token
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                "Unable to skip queue {$queueId}."
            );
        }
    }


    // Retry failures known to have occurred before SMTP submission.
    public function markForRetry(
        int $queueId,
        string $token,
        string $error,
        int $maxAttempts = 3
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE teacher_notification_queue
            SET status = CASE
                    WHEN attempts >= ? THEN 'failed'
                    ELSE 'pending'
                END,
                last_error = ?,
                next_attempt_at = CASE
                    WHEN attempts >= ? THEN NULL
                    ELSE DATE_ADD(
                        NOW(),
                        INTERVAL (attempts * 5) MINUTE
                    )
                END,
                processed_at = CASE
                    WHEN attempts >= ? THEN NOW()
                    ELSE NULL
                END,
                claimed_at = NULL,
                claim_token = NULL
            WHERE queue_id = ?
              AND status = 'processing'
              AND claim_token = ?
        ");

        $stmt->execute([
            $maxAttempts,
            mb_substr($error, 0, 1000),
            $maxAttempts,
            $maxAttempts,
            $queueId,
            $token
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                "Unable to reschedule queue {$queueId}."
            );
        }
    }


    // Permanently stop a report that cannot be processed.
    public function markAsFailed(
        int $queueId,
        string $token,
        string $reason
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE teacher_notification_queue
            SET status = 'failed',
                last_error = ?,
                processed_at = NOW(),
                next_attempt_at = NULL,
                claimed_at = NULL,
                claim_token = NULL
            WHERE queue_id = ?
              AND status = 'processing'
              AND claim_token = ?
        ");

        $stmt->execute([
            mb_substr($reason, 0, 1000),
            $queueId,
            $token
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                "Unable to fail queue {$queueId}."
            );
        }
    }


    // Identify jobs interrupted during processing.
    // Do not automatically resend them because SMTP delivery may be uncertain.
    public function recoverStaleReports(
        int $minutes = 15
    ): int {

        $minutes = max(1, $minutes);

        $stmt = $this->pdo->prepare("
            UPDATE teacher_notification_queue
            SET status = 'failed',
                last_error = 'STALE_PROCESSING_REVIEW_REQUIRED',
                processed_at = NOW(),
                next_attempt_at = NULL,
                claimed_at = NULL,
                claim_token = NULL
            WHERE status = 'processing'
              AND claimed_at < DATE_SUB(
                  NOW(),
                  INTERVAL ? MINUTE
              )
        ");

        $stmt->bindValue(1, $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }


}
