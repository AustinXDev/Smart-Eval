<?php

declare(strict_types=1);

use App\Core\Container;
use App\providers\EmailProvider;
use App\Repositories\NotificationRepositories\TeacherNotificationRepository;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

// Resolve relative paths from this script, not the cron working directory.
require_once dirname(__DIR__) . '/app/init.php';

umask(0077);
$logFile = dirname(__DIR__) . '/storage/logs/teacher-report.log';
$logDir = dirname($logFile);
if (!is_dir($logDir) && !mkdir($logDir, 0700, true) && !is_dir($logDir)) {
    fwrite(STDERR, "Unable to initialize teacher report log directory.\n");
    exit(1);
}

function teacherReportLog(string $message): void
{
    global $logFile;
    $entry = '[' . date('Y-m-d H:i:s') . '] [TeacherReportCron] ' . $message . PHP_EOL;
    if (file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) === false) {
        error_log('[TeacherReportCron] Unable to write teacher report log.');
    }
}


$batchSize = 5;
$maxAttempts = 3;
$dryRun = ($_ENV['TEACHER_REPORT_DRY_RUN'] ?? 'true') !== 'false';
$sendTest = ($_ENV['TEACHER_REPORT_SEND_TEST'] ?? 'false') === 'true';
$testEmail = trim((string) ($_ENV['TEACHER_REPORT_TEST_EMAIL'] ?? ''));
$productionEnabled = ($_ENV['TEACHER_REPORT_PRODUCTION_ENABLED'] ?? 'false') === 'true';

// Explicitly reject conflicting or dangerous configuration.
if (!$dryRun && (!$productionEnabled || $sendTest)) {
    teacherReportLog("Configuration rejected: incompatible production flags.");
    fwrite(STDERR, "Invalid production worker configuration.\n");
    exit(1);
}
if ($sendTest && !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    teacherReportLog("Configuration rejected: invalid test recipient.");
    fwrite(STDERR, "A valid TEACHER_REPORT_TEST_EMAIL is required.\n");
    exit(1);
}

// flock prevents overlapping instances on the SAME server/filesystem.
// The database claim token remains the cross-process ownership safeguard.
$lockPath = sys_get_temp_dir() . '/smart_eval_teacher_reports.lock';
$lock = fopen($lockPath, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    teacherReportLog("Worker skipped: another instance running or lock unavailable.");
    fwrite(STDERR, "Teacher report worker is already running or lock unavailable.\n");
    exit(1);
}

$hadErrors = false;
$stats = ['claimed' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0, 'retry' => 0, 'test' => 0];
teacherReportLog('Worker started; mode=' . ($dryRun ? ($sendTest ? 'test-email' : 'dry-run') : 'production'));

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $container = new Container($pdo);
    $reportService = $container->reportService();
    $notificationRepository = new TeacherNotificationRepository($pdo);
    $emailProvider = ($sendTest || !$dryRun) ? new EmailProvider() : null;

    if (!$dryRun) {
        $recovered = $notificationRepository->recoverStaleReports(15);
        if ($recovered > 0) {
            teacherReportLog("Stale notifications requiring review: {$recovered}.");
            echo "[RECOVERY] {$recovered} notifications require review.\n";
        }
    }

    $jobs = $notificationRepository->getPendingReports($batchSize, $maxAttempts);
    echo 'Pending reports: ' . count($jobs) . PHP_EOL;
    teacherReportLog('Pending jobs selected=' . count($jobs));

    foreach ($jobs as $job) {
        $queueId = (int) $job['queue_id'];
        $teacherId = (int) $job['teacher_id'];
        $periodId = (int) $job['period_id'];
        $token = null;
        $claimed = false;
        $smtpStarted = false;
        $smtpAccepted = false;
        $pdf = null;

        try {
            if (!$dryRun) {
                $token = bin2hex(random_bytes(16));
                $claimed = $notificationRepository->claimReport($queueId, $token, $maxAttempts);
                if (!$claimed) {
                    teacherReportLog("queue={$queueId} claim rejected or ineligible");
                    echo "[IGNORED] Queue {$queueId}: Already claimed or ineligible.\n";
                    continue;
                }
                $stats['claimed']++;
            } else {
                echo "[DRY RUN] Checking Queue {$queueId}\n";
            }

            $teacher = $notificationRepository->getTeacher($teacherId);
            $skipReason = null;
            if (!$teacher || (int) $teacher['is_active'] !== 1) {
                $skipReason = 'TEACHER_INACTIVE_OR_NOT_FOUND';
            } elseif (!filter_var(trim((string) ($teacher['email'] ?? '')), FILTER_VALIDATE_EMAIL)) {
                $skipReason = 'INVALID_TEACHER_EMAIL';
            } elseif (!$notificationRepository->hasReportableResponses($teacherId, $periodId)) {
                $skipReason = 'NO_EVALUATION_RESPONSES';
            }

            if ($skipReason !== null) {
                if (!$dryRun) {
                    $notificationRepository->markAsSkipped($queueId, $token, $skipReason);
                }
                $stats['skipped']++;
                teacherReportLog("queue={$queueId} skipped reason={$skipReason} mode=" . ($dryRun ? 'dry-run' : 'production'));
                echo "[SKIPPED] Queue {$queueId}: {$skipReason}\n";
                continue;
            }

            $pdf = $reportService->generateIndiviudalTeacherReport($periodId, $teacherId);
            if (!is_string($pdf) || !str_starts_with($pdf, '%PDF-')) {
                throw new RuntimeException('Invalid PDF generated.');
            }

            echo "[PDF] Queue {$queueId}: " . round(strlen($pdf) / 1024, 2) . " KB\n";
            teacherReportLog("queue={$queueId} pdf_generated bytes=" . strlen($pdf));
            if ($dryRun && !$sendTest) {
                continue;
            }

            $teacherName = preg_replace(
                '/[^\p{L}\p{N}_-]+/u',
                '_',
                trim((string) $teacher['full_name'])
            );
            $teacherName = trim((string) $teacherName, '_');
            $teacherName = mb_substr($teacherName !== '' ? $teacherName : 'Teacher', 0, 80);
            $filename = "Teacher_Report_{$teacherId}_{$teacherName}_Period_{$periodId}.pdf";

            $recipient = $dryRun ? $testEmail : trim((string) $teacher['email']);
            $subject = $dryRun
                ? '[TEST] Smart-Eval Teacher Evaluation Report'
                : 'Smart-Eval | Individual Teacher Evaluation Report';
            $body = $dryRun
                ? '<p>This is a Smart-Eval test email. No queue status was changed.</p>'
                : '<p>Greetings!</p><p>Your individual teacher evaluation report is attached as a PDF.</p><p>Thank you.</p>';

            // From this point, an exception may mean the provider accepted the email.
            $smtpStarted = true;
            $sent = $emailProvider->sendWithPdf($recipient, $subject, $body, $pdf, $filename);
            if ($sent !== true) {
                throw new RuntimeException('SMTP submission was not confirmed.');
            }
            $smtpAccepted = true;

            if ($dryRun) {
                $stats['test']++;
                teacherReportLog("queue={$queueId} test_email_accepted queue_unchanged");
                echo "[TEST] Queue {$queueId}: SMTP accepted test message; queue unchanged.\n";
                continue;
            }

            // Do not send again if this update fails: SMTP already accepted the email.
            $notificationRepository->markAsSent($queueId, $token);
            $stats['sent']++;
            teacherReportLog("queue={$queueId} smtp_accepted status=sent");
            echo "[SENT] Queue {$queueId}: Report accepted by SMTP.\n";
        } catch (Throwable $e) {
            $hadErrors = true;
            $reason = $smtpAccepted
                ? 'SMTP_ACCEPTED_STATUS_UPDATE_FAILED'
                : ($smtpStarted ? 'SMTP_DELIVERY_UNCERTAIN' : null);
            teacherReportLog("queue={$queueId} error=" . get_class($e) . " stage=" .
                ($smtpAccepted ? 'post-smtp' : ($smtpStarted ? 'smtp-uncertain' : 'pre-smtp')));
            error_log("[TeacherReportWorker] Queue {$queueId}: " . $e->getMessage());

            if ($claimed) {
                try {
                    if ($reason !== null) {
                        $notificationRepository->markAsFailed($queueId, $token, $reason);
                        $stats['failed']++;
                        teacherReportLog("queue={$queueId} status=failed reason={$reason}");
                    } else {
                        $notificationRepository->markForRetry(
                            $queueId,
                            $token,
                            $e->getMessage(),
                            $maxAttempts
                        );
                        $stats['retry']++;
                        teacherReportLog("queue={$queueId} pre_smtp_failure retry_or_terminal_state_updated");
                    }
                } catch (Throwable $statusError) {
                    teacherReportLog("queue={$queueId} status_update_failed type=" . get_class($statusError));
                    error_log("[TeacherReportWorker] Queue {$queueId}: Failed to update queue state: " . $statusError->getMessage());
                    // A processing record left behind must be reviewed by stale recovery.
                }
            }
            echo "[ERROR] Queue {$queueId}: Processing failed; see PHP error log.\n";
        } finally {
            unset($pdf);
        }
    }

    echo $dryRun ? "Teacher report dry-run completed.\n" : "Teacher report worker completed.\n";
} catch (Throwable $e) {
    $hadErrors = true;
    teacherReportLog("Worker fatal error: " . get_class($e));
    error_log('[TeacherReportWorker] Fatal: ' . $e->getMessage());
    fwrite(STDERR, "Teacher report worker failed; see PHP error log.\n");
} finally {
    teacherReportLog(sprintf(
        'Worker completed claimed=%d sent=%d skipped=%d failed=%d retry_updates=%d test_emails=%d errors=%s',
        $stats['claimed'],
        $stats['sent'],
        $stats['skipped'],
        $stats['failed'],
        $stats['retry'],
        $stats['test'],
        $hadErrors ? 'yes' : 'no'
    ));
    flock($lock, LOCK_UN);
    fclose($lock);
}

exit($hadErrors ? 1 : 0);
