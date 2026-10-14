<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

require_once dirname(__DIR__, 2) . '/app/init.php';

use App\Repositories\NotificationRepositories\TeacherNotificationRepository;

$queueId = 34;

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $repository = new TeacherNotificationRepository($pdo);

    // Verify that the database table supports transactions.
    $engineStmt = $pdo->query("
        SELECT ENGINE
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'teacher_notification_queue'
    ");

    if (strtoupper((string) $engineStmt->fetchColumn()) !== 'INNODB') {
        throw new RuntimeException('Queue table must use InnoDB.');
    }

    // Protect the original queue record with a transaction.
    $pdo->beginTransaction();

    $token = bin2hex(random_bytes(16));

    if (!$repository->claimReport($queueId, $token, 3)) {
        throw new RuntimeException('Unable to claim test notification.');
    }

    // Verify skipped notifications.
    $repository->markAsSkipped(
        $queueId,
        $token,
        'TEST_SKIP'
    );

    $stmt = $pdo->prepare("
        SELECT status
        FROM teacher_notification_queue
        WHERE queue_id = ?
    ");
    $stmt->execute([$queueId]);

    if ($stmt->fetchColumn() !== 'skipped') {
        throw new RuntimeException('Skip status test failed.');
    }

    echo "[PASS] markAsSkipped()" . PHP_EOL;

    // Restore the test record within the transaction.
    $pdo->prepare("
        UPDATE teacher_notification_queue
        SET status = 'pending',
            attempts = 0,
            processed_at = NULL,
            last_error = NULL,
            next_attempt_at = NULL,
            claimed_at = NULL,
            claim_token = NULL
        WHERE queue_id = ?
    ")->execute([$queueId]);

    $token = bin2hex(random_bytes(16));

    if (!$repository->claimReport($queueId, $token, 3)) {
        throw new RuntimeException('Unable to claim retry test notification.');
    }

    // Verify retry scheduling.
    $repository->markForRetry(
        $queueId,
        $token,
        'TEST_PDF_FAILURE',
        3
    );

    $stmt = $pdo->prepare("
        SELECT status, attempts, next_attempt_at
        FROM teacher_notification_queue
        WHERE queue_id = ?
    ");
    $stmt->execute([$queueId]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $result['status'] !== 'pending'
        || (int) $result['attempts'] !== 1
        || $result['next_attempt_at'] === null
    ) {
        throw new RuntimeException('Retry scheduling test failed.');
    }

    echo "[PASS] markForRetry()" . PHP_EOL;

    // Restore the test record for the failure test.
    $pdo->prepare("
        UPDATE teacher_notification_queue
        SET status = 'pending',
            attempts = 0,
            next_attempt_at = NULL,
            last_error = NULL,
            processed_at = NULL,
            claimed_at = NULL,
            claim_token = NULL
        WHERE queue_id = ?
    ")->execute([$queueId]);

    $token = bin2hex(random_bytes(16));

    if (!$repository->claimReport($queueId, $token, 3)) {
        throw new RuntimeException('Unable to claim failure test notification.');
    }

    // Verify permanent failure handling.
    $repository->markAsFailed(
        $queueId,
        $token,
        'TEST_SMTP_UNCERTAIN'
    );

    $stmt = $pdo->prepare("
        SELECT status
        FROM teacher_notification_queue
        WHERE queue_id = ?
    ");
    $stmt->execute([$queueId]);

    if ($stmt->fetchColumn() !== 'failed') {
        throw new RuntimeException('Failure status test failed.');
    }

    echo "[PASS] markAsFailed()" . PHP_EOL;

    // Restore the test record for stale recovery.
    $pdo->prepare("
        UPDATE teacher_notification_queue
        SET status = 'processing',
            attempts = 1,
            claimed_at = DATE_SUB(NOW(), INTERVAL 20 MINUTE),
            claim_token = ?,
            processed_at = NULL,
            last_error = NULL
        WHERE queue_id = ?
    ")->execute([
        bin2hex(random_bytes(16)),
        $queueId
    ]);

    // Verify stale processing recovery.
    $repository->recoverStaleReports(15);

    $stmt = $pdo->prepare("
        SELECT status, last_error
        FROM teacher_notification_queue
        WHERE queue_id = ?
    ");
    $stmt->execute([$queueId]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $result['status'] !== 'failed'
        || $result['last_error'] !== 'STALE_PROCESSING_REVIEW_REQUIRED'
    ) {
        throw new RuntimeException('Stale recovery test failed.');
    }

    echo "[PASS] recoverStaleReports()" . PHP_EOL;

} catch (Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . PHP_EOL;
    exitCode:
} finally {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "[ROLLBACK] All test changes reverted." . PHP_EOL;
    }
}
