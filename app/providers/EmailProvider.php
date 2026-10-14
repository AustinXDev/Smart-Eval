<?php

namespace App\providers;

require_once __DIR__ . '/../init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailProvider
{
    private $mail;

    public function __construct()
    {

        $this->mail = new PHPMailer();

        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['SMTP_USERNAME'];
        $this->mail->Password = $_ENV['SMTP_PASSWORD'];
        $this->mail->SMTPSecure =
          $_ENV['SMTP_ENCRYPTION'] === 'tls'
          ? PHPMailer::ENCRYPTION_STARTTLS
          : PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = (int) $_ENV['SMTP_PORT'];

        $this->mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $this->mail->isHTML(true);

    }

    public function send($to, $subject, $body): bool
    {
        // Clear data from previous email requests.
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();

        try {

            // Prepare the email content.
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            // Send the email.
            return $this->mail->send();

        } catch (\Throwable $e) {
            error_log(
                '[EmailProvider] Email sending failed: '
                . $e->getMessage()
            );

            return false;
        } finally {

            // Clear recipients after sending.
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();

        }
    }

    public function sendWithPdf(
        string $to,
        string $subject,
        string $body,
        string $pdfContent,
        string $filename
    ): bool {

        // Clear recipients and attachments from previous emails.
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();

        // Check if the generated content is a valid PDF.
        if (!str_starts_with($pdfContent, '%PDF-')) {
            error_log('[EmailProvider] Invalid PDF content.');
            return false;
        }

        try {

            //Sent the recipient and email content
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);

            //Attach the generated PDF directly from memory.
            $this->mail->addStringAttachment(
                $pdfContent,
                $filename,
                'base64',
                'application/pdf'
            );

            // Send the teacher report.
            return $this->mail->send();

        } catch (\Throwable $e) {

            error_log(
                '[EmailProvider] PDF email failed: '
                . $e->getMessage()
            );

            return false;

        } finally {

            // Release attachment data after sending.
            $this->mail->clearAttachments();
            $this->mail->clearAllRecipients();

        }

    }

}
