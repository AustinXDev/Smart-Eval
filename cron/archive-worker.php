<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require_once __DIR__ . '/../app/init.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/config/archive-database.php';

use App\Repositories\ArchiveRepo\ArchiveRepository;
use App\Services\BackupServices\GoogleDriveService;
use App\Services\BackupServices\ArchiveBackupService;
use App\Services\ArchiveServices\ArchiveCleanupService;
use App\Services\ArchiveServices\ArchiveService;
use App\Services\ArchiveServices\ArchiveAutomationService;
use App\Services\BackupServices\GoogleTokenStorage;
use App\Services\BackupServices\DatabaseExportService;
use App\Services\BackupServices\BackupEncryptionService;
use App\Services\ArchiveServices\ArchiveEligibilityService;
use App\Services\ArchiveServices\ArchiveReadinessService;
use App\Services\ArchiveServices\ArchiveValidationService;

$logFile = __DIR__ . '/../storage/logs/archive.log';

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

$lockHandle = null;

cronLog('Archive started.');

ob_start();

try {

    // Confirm database connections.
    if (
        !isset($pdo, $archivePdo) ||
        !$pdo instanceof PDO ||
        !$archivePdo instanceof PDO
    ) {
        throw new RuntimeException(
            'Live or archive database connection is unavailable.'
        );
    }

    // Configure backup directory.
    $backupDirectory = __DIR__ . '/../storage/backups';

    if (
        !is_dir($backupDirectory) &&
        !mkdir($backupDirectory, 0700, true) &&
        !is_dir($backupDirectory)
    ) {
        throw new RuntimeException(
            'Unable to create backup directory.'
        );
    }

    // Prevent overlapping worker executions.
    $lockPath = $backupDirectory . '/archive-worker.lock';

    $lockHandle = fopen($lockPath, 'c');

    if ($lockHandle === false) {
        throw new RuntimeException(
            'Unable to open archive worker lock.'
        );
    }

    if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
        echo "[SKIPPED] Archive worker is already running.\n";
        exit(0);
    }

    $folderId = null;

    // Initialize archive repository.
    $archiveRepo = new ArchiveRepository(
        $pdo,
        $archivePdo
    );

    // Initialize archive validation services.
    $eligibilityService = new ArchiveEligibilityService(
        $archiveRepo
    );

    // Initialize archive service.
    $archiveService = new ArchiveService(
        $pdo,
        $archivePdo,
        $eligibilityService
    );

    // Initialize database export.
    $databaseExportService = new DatabaseExportService(
        $archivePdo,
        $backupDirectory
    );

    // Retrieve encryption key.
    $encodedKey = $_ENV['BACKUP_ENCRYPTION_KEY'] ?? null;

    if (
        !is_string($encodedKey) ||
        $encodedKey === ''
    ) {
        throw new RuntimeException(
            'Backup encryption key is not configured.'
        );
    }

    $encryptionKey = base64_decode(
        $encodedKey,
        true
    );

    if (
        $encryptionKey === false ||
        strlen($encryptionKey) !== 32
    ) {
        throw new RuntimeException(
            'Backup encryption key must decode to exactly 32 bytes.'
        );
    }

    // Initialize encryption service.
    $encryptionService = new BackupEncryptionService(
        $encryptionKey
    );

    // Initialize Google Drive services.
    $tokenStorage = new GoogleTokenStorage();

    $googleService = new GoogleDriveService(
        $tokenStorage
    );

    // Initialize backup service.
    $backupService = new ArchiveBackupService(
        $archivePdo,
        $databaseExportService,
        $encryptionService,
        $googleService
    );

    $readinessService = new ArchiveReadinessService(
        $archiveRepo
    );

    $validationService = new ArchiveValidationService(
        $archiveRepo,
        $backupService
    );

    // Initialize cleanup service.
    $cleanupService = new ArchiveCleanupService(
        $pdo,
        $archiveRepo,
        $eligibilityService,
        $readinessService,
        $validationService
    );

    // Initialize automation service.
    $automationService = new ArchiveAutomationService(
        $archiveRepo,
        $archiveService,
        $backupService,
        $cleanupService,
        $readinessService
    );

    echo "[SUCCESS] Worker initialization completed.\n";

    // Execute automatic archive workflow.
    $summary = $automationService->run();

    cronLog(
        "[SUMMARY] Eligible: {$summary['eligible']}"
        . " | Completed: {$summary['completed']}"
        . " | Skipped: {$summary['skipped']}"
        . " | Failed: {$summary['failed']}"
    );

    if ($summary['failed'] > 0) {
        throw new RuntimeException(
            "{$summary['failed']} archive period(s) failed."
        );
    }

} catch (Throwable $e) {


    error_log(
        '[ARCHIVE WORKER] ' . $e->getMessage()
    );

    cronLog('[ERROR] ' . $e->getMessage());

    exit(1);

} finally {

    $output = ob_get_clean();

    if ($output !== false && trim($output) !== '') {
        echo $output;

        foreach (preg_split('/\r\n|\r|\n/', trim($output)) as $line) {
            cronLog($line);
        }
    }
}
