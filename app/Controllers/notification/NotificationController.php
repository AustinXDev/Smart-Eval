<?php 
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Models/AnalyticsModel.php';
require_once __DIR__ . '/../../Controllers/reports/utils/PdfRenderer.php';

class NotificationController {

    private function getActivePeriodId($dept) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT period_id FROM evaluation_periods WHERE target_dept = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$dept]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['period_id'] : null;
    }

    public function prepareQueue($dept) {
        global $pdo;
        $periodId = $this->getActivePeriodId($dept);
        
        if (!$periodId) {
            echo json_encode(['status' => 'error', 'message' => 'No active period found for ' . $dept]);
            return;
        }

        $sql = "INSERT IGNORE INTO notification_batches (period_id, student_id, status)
                SELECT ?, s.student_id, 'pending'
                FROM students s
                JOIN programs p ON s.program_id = p.program_id
                LEFT JOIN evaluation_status es ON s.student_id = es.student_id AND es.period_id = ?
                WHERE p.department = ? 
                  AND s.is_active = 1 
                  AND es.student_id IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$periodId, $periodId, $dept]);

        $countStmt = $pdo->prepare(
          "SELECT COUNT(*) FROM notification_batches WHERE period_id = ? AND status = 'pending'"
        );
        $countStmt->execute([$periodId]);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode(['status' => 'success', 'total' => $total]);
    }

    public function processBatch($dept) {
        global $pdo;
        $periodId = $this->getActivePeriodId($dept);
        
        if (!$periodId) {
            echo json_encode(['status' => 'error', 'message' => 'Active period missing.']);
            return;
        }

        set_time_limit(60); 
        $limit = 25; 

        $sql = "SELECT nb.batch_id, s.email, s.full_name
                FROM notification_batches nb
                JOIN students s ON nb.student_id = s.student_id
                WHERE nb.period_id = ? AND nb.status = 'pending'
                LIMIT ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$periodId, $limit]);
        $batch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($batch)) {
            echo json_encode(['status' => 'finished', 'message' => 'All notifications sent.']);
            return;
        }

        $batchErrors = [];
        $sentCount = 0;

        foreach ($batch as $row) {
            if  ($row['email'] === ''){
              $update = $pdo->prepare("UPDATE notification_batches SET status = 'failed' WHERE batch_id = ?");
              $update->execute([$row['batch_id']]);
              $batchErrors[] = [
                  'name'     => $row['full_name'],
                  'reason'   => 'No email address on record'
              ];
            }
            elseif(!filter_var($row['email'], FILTER_VALIDATE_EMAIL)){
              $update = $pdo->prepare("UPDATE notification_batches SET status = 'failed' WHERE batch_id = ?");
              $update->execute([$row['batch_id']]);
              $batchErrors[] = [
                  'name'     => $row['full_name'],
                  'reason'   => 'Invalid email format'
              ];
            }
            elseif ($this->sendEmail($row['email'], $row['full_name'])) {
                $update = $pdo->prepare("UPDATE notification_batches SET status = 'sent', sent_at = NOW() WHERE batch_id = ?");
                $update->execute([$row['batch_id']]);
                $sentCount++;
            } else {
                $update = $pdo->prepare("UPDATE notification_batches SET status = 'failed' WHERE batch_id = ?");
                $update->execute([$row['batch_id']]);
            }
        }

        echo json_encode([
            'status' => 'processing',
            'sent' => $sentCount,
            'errors' => $batchErrors,
            'hasMore' => true
        ]);
    }

    public function sendEmail($toEmail, $toName) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'smarteval02@gmail.com';
            $mail->Password   = 'jfzw tmgp imah iukq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->SMTPDebug  = 0;

            $mail->setFrom('smarteval02@gmail.com', 'SmartEval');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Urgent: Evaluation Period is Closing Soon';
            $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 400px; margin: auto;'>

              <h2 style='color: #4a0080;'>SmartEval Participation Reminder</h2>

              <p>Hi <strong>{$toName}</strong>,</p>

              <p>
                  Our records show that you have <strong>not yet participated</strong> 
                  in the <strong>Teacher Evaluation</strong>.
              </p>

              <p>
                  Your participation is important to ensure complete and accurate results.
              </p>

              <p>
                  Please log in to the SmartEval system and complete your participation 
                  before the deadline.
              </p>

              <p style='color: red;'>
                  If you believe you already participated or received this message by mistake,
                  please contact your administrator immediately.
              </p>

              <p style='font-size: 12px; color: #555;'>
                  This is an automated notification from Smart-Eval System.
              </p>

              </div>";

            return $mail->send(); 
        } catch (Exception $e) {
            return false;
        }
      }

      public function processQueue() {
          global $pdo;
          $model = new AnalyticsModel($pdo);
          $renderer = new PdfRenderer();

          set_time_limit(300); 
          ini_set('memory_limit', '512M');

          $stmt = $pdo->query("
            SELECT q.queue_id, q.status, q.teacher_id,   
            q.period_id, t.email, t.full_name 
            FROM teacher_notification_queue q
            JOIN teachers t ON q.teacher_id = t.teacher_id
            WHERE q.status = 'pending' LIMIT 10"
          );
          $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if(empty($tasks)) return;

          $mail = new PHPMailer(true);
          try {
              $mail->isSMTP();
              $mail->Host       = 'smtp.gmail.com'; 
              $mail->SMTPAuth   = true;
              $mail->Username   = 'smarteval02@gmail.com'; 
              $mail->Password   = 'jfzw tmgp imah iukq'; 
              $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
              $mail->Port       = 587;
              $mail->setFrom('smarteval02@gmail.com', 'SMART EVAL System');
              $mail->isHTML(true);
              $mail->SMTPKeepAlive = true;
          } catch (Exception $e) {
              error_log("Mailer Setup Error: " . $e->getMessage());
              return;
          }

          foreach($tasks as $task) {
            $tempPath = __DIR__ . "/../../../storage/temp/report_{$task['teacher_id']}.pdf";

            if($task['status'] === 'sent'){
              continue;
            }
          
            try {
              $data = $model->getIndividualTeacherBundle($task['period_id'], $task['teacher_id']);
              if(!$data) continue;

              $pdfBinary = $renderer->getPdfBinary('teacher_individual_report.php', [
                  'data' => $data,
                  'meta' => ['is_live' => false, 'generated_at' => date('Y-m-d')]
              ]);
              
              file_put_contents($tempPath, $pdfBinary);

              $mail->clearAddresses();
              $mail->clearAttachments();
              $mail->addAddress($task['email'], $task['full_name']);
              $mail->addAttachment($tempPath, "Evaluation_Report_" . date('Y-m-d') . ".pdf");

              $mail->Subject = "Your Evaluation Results - " . date('Y-m-d');
              $mail->Body    = "Dear {$task['full_name']}, <br><br> Attached is your evaluation report for the recent period. <br><br> Best regards, <br> Smart-Eval System";

              if($mail->send()) {
                  $update = $pdo->prepare("UPDATE teacher_notification_queue SET status='sent', processed_at=NOW() WHERE  queue_id=?");
                  $update->execute([$task['queue_id']]);
              }

            } catch (Exception $e) {
              $pdo->prepare("UPDATE teacher_notification_queue SET status='failed', last_error = ? WHERE queue_id=?")
                  ->execute([substr($e->getMessage(), 0, 500), $task['queue_id']]);
            } finally {
              if (file_exists($tempPath)) {
                  unlink($tempPath);
              }
            }
          }

          $mail->smtpClose();

          error_log("[processQueue] Batch complete. Processed " . count($tasks) . " task(s).");
      }
}

// Router Logic
if (isset($_GET['action'])) {
    $controller = new NotificationController();
    $dept = $_GET['dept'] ?? null;

    ob_end_clean();
    header('Content-Type: application/json');

    try {
        if ($_GET['action'] === 'prepare') {
            $controller->prepareQueue($dept);
        } elseif ($_GET['action'] === 'process') {
            $controller->processBatch($dept);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
        }
    } catch (Throwable $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    exit;
}