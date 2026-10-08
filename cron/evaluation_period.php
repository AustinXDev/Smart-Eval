<?php

require_once __DIR__ . '/../app/init.php';

use App\Controllers\periods\EvaluationController;
use App\Services\EvaluationServices\EvaluationService;
use App\Repositories\EvaluationRepo\EvaluationRepository;

require_once __DIR__ . '/../app/config/database.php';

$repository = new EvaluationRepository($pdo);
$service = new EvaluationService($repository, $pdo);
$controller = new EvaluationController($service);

$logFile = __DIR__ . '/../storage/logs/cron.log';

// CORRECTED: Extract the directory path ('../storage/logs') from the file path
$logDir = dirname($logFile);

// Check if the directory exists; if not, create it recursively
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

cronLog('Cron started');

try {

    cronLog('PHP timezone: ' . date_default_timezone_get());
    cronLog('PHP current time: ' . date('Y-m-d H:i:s'));

    $controller->runAutomaticUpdate();

    cronLog('Cron completed successfully');
} catch (Throwable $e) {
    cronLog(
        'Cron failed: ' .
        $e->getMessage() . PHP_EOL .
        'Stack Trace: ' . $e->getTraceAsString() // Added for better troubleshooting
    );
}
