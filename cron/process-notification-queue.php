<?php


declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

set_time_limit(240);
umask(0077);

require_once dirname(__DIR__) . '/app/init.php';

use App\providers\EmailProvider;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\NotificationRepositories\NotificationRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\NotificationServices\NotificationWorkerService;

$logFile = __DIR__ . '/../storage/logs/notification.log';

$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function cronLog(string $message): void
{
    global $logFile;

    file_put_contents(
        $logFile,
        '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
        FILE_APPEND
    );
}

try {

    /*
       * 1. Verify that the application initialized the database.
       * Adjust this if your init.php uses a database factory instead.
       */
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException(
            'Database connection is unavailable.'
        );
    }


    #Initialize dependencies

    $notificationRepository = new NotificationRepository($pdo);
    $evaluationRepository   = new EvaluationRepository($pdo);
    $studentRepository      = new StudentRepository($pdo);
    $emailProvider         = new EmailProvider();


    # Callback: check whether the evaluation period is open.

    $isPeriodOpen = static function (
        int $periodId
    ) use ($evaluationRepository): bool {
        return $evaluationRepository->isPeriodOpen($periodId);
    };


    #Callback: Check whether this student still needs to evaluate.
    $stillNeedsEvaluation = static function (
        string $studentId,
        int $periodId
    ) use ($evaluationRepository, $studentRepository): bool {

        $student = $studentRepository->findById($studentId);

        if (!$student) {
            return false;
        }

        $department = $student->department;

        if (!$department) {
            return false;
        }

        return $evaluationRepository->stillNeedsEvaluation(
            $studentId,
            $periodId,
            $department
        );

    };

    $sendEmail = static function (
        string $studentId,
        int $periodId,
        string $type
    ) use (
        $studentRepository,
        $evaluationRepository,
        $emailProvider
    ): void {

        $student = $studentRepository->findById($studentId);

        if (!$student || empty($student->email)) {
            throw new RuntimeException(
                'Student record or email address is unavailable.'
            );
        }

        // Only process supported notification types.
        if ($type !== 'evaluation_reminder') {
            throw new RuntimeException('Unsupported notification type.');
        }

        // Retrieve the specific evaluation period.
        $period = $evaluationRepository->getById($periodId);

        if (!$period) {
            throw new RuntimeException('Evaluation period not found.');
        }

        $name = htmlspecialchars(
            (string) $student->fullName,
            ENT_QUOTES,
            'UTF-8'
        );

        $subject = 'Reminder: Complete Your Teacher Evaluation';

        $body = "
        <!DOCTYPE html>
        <html lang=\"en\">
        <head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <meta name=\"x-apple-disable-message-reformatting\">
            <title>Teacher Evaluation Reminder</title>
        </head>
        <body style=\"margin:0; padding:0; background-color:#F3F4F6; font-family:Arial, Helvetica, sans-serif; color:#16213E;\">
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-collapse:collapse; background-color:#F3F4F6;\">
                <tr>
                    <td align=\"center\" style=\"padding:32px 16px;\">
                        <table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background-color:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px;\">
                            <tr>
                                <td style=\"padding:28px 32px 12px; border-top:5px solid #7C3AED; border-radius:12px 12px 0 0;\">
                                    <p style=\"margin:0 0 8px; color:#5B21B6; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;\">Smart-Eval</p>
                                    <h1 style=\"margin:0; color:#16213E; font-size:24px; line-height:1.3; font-weight:700;\">Teacher Evaluation Reminder</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style=\"padding:12px 32px 28px; color:#374151; font-size:15px; line-height:1.7;\">
                                    <p style=\"margin:0 0 16px;\">Hello {$name},</p>
                                    <p style=\"margin:0 0 24px;\">You have pending teacher evaluations to complete for the applicable evaluation period. Please sign in to Smart-Eval and submit your evaluations when convenient.</p>
                                    <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:separate; border-spacing:0;\">
                                        <tr>
                                            <td align=\"center\" bgcolor=\"#7C3AED\" style=\"background-color:#7C3AED; border-radius:8px;\">
                                                <a href=\"" . htmlspecialchars(BASE_URL . 'Login', ENT_QUOTES, 'UTF-8') . "\" style=\"display:inline-block; padding:13px 22px; border:1px solid #7C3AED; border-radius:8px; color:#FFFFFF; font-size:14px; line-height:1.2; font-weight:bold; text-decoration:none;\">Login to Smart-Eval</a>
                                            </td>
                                        </tr>
                                    </table>
                                    <p style=\"margin:24px 0 0;\">Thank you for your time and participation in the evaluation process.</p>
                                    <p style=\"margin:18px 0 0; color:#6B7280; font-size:13px;\">Smart-Eval<br>Asian Institute of Technology and Education</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
    ";

        if (!$emailProvider->send(
            (string) $student->email,
            $subject,
            $body
        )) {
            throw new RuntimeException(
                'Email provider returned a sending failure.'
            );
        }
    };

    $worker = new NotificationWorkerService(
        $notificationRepository,
        $isPeriodOpen,
        $stillNeedsEvaluation,
        $sendEmail
    );

    $result = $worker->run(10);

    // Log counts only. except student emails or credentials.

    cronLog(sprintf(
        '[NotificationCron] claimed=%d sent=%d skipped=%d failed=%d',
        $result['claimed'],
        $result['sent'],
        $result['skipped'],
        $result['failed']
    ));

} catch (Throwable $e) {

    cronLog(
        '[NotificationCron] Worker stopped: '
          . get_class($e)
          . ' - '
          . $e->getMessage()
    );

    exit(1);

}

exit(0);
