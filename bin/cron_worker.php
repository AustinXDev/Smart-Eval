<?php 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Models/AnalyticsModel.php';
require_once __DIR__ . '/../app/Controllers/notification/NotificationController.php';
require_once __DIR__ . '/../app/Controllers/reports/utils/PdfRenderer.php';
require_once __DIR__ . '/../vendor/autoload.php';

$notification = new NotificationController();

$notification->processQueue();

file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - Script Ran\n", FILE_APPEND);
?>