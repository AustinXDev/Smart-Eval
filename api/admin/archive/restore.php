<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/init.php';
require_once dirname(__DIR__, 3) . '/app/config/archive-database.php';

use App\Services\ArchiveServices\ArchiveRecoveryService;
use App\Services\BackupServices\BackupEncryptionService;
use App\Services\BackupServices\DatabaseRestoreService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jsonResponse(
    int $code,
    string $status,
    string $message,
    array $data = []
): never {

    http_response_code($code);

    echo json_encode([
      'status' => $status,
      'message' => $message,
      'data' => $data
    ], JSON_THROW_ON_ERROR);

    exit;

}


try {

    // Allow POST request only.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');

        jsonResponse(
            405,
            'error',
            'Method not allowed.'
        );
    }

    // Authenticate administrator.
    if (
        empty($_SESSION['admin_id']) ||
        $_SESSION['admin_authenticated'] !== true
    ) {
        jsonResponse(
            401,
            'error',
            'Administrator authentication required.'
        );
    }

    // Authorize Super Admin only.
    // Replace this with your actual role validation.
    if (($_SESSION['role'] ?? '') !== 'super admin') {
        jsonResponse(
            403,
            'error',
            'Only Super Admin can restore backups.'
        );
    }

    // Validate period ID.
    $periodId = filter_var(
        $_POST['period_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($periodId === false || $periodId <= 0) {
        jsonResponse(
            422,
            'error',
            'Invalid evaluation period.'
        );
    }

    // Validate upload file.
    $uploadedFile = $_FILES['backup_file'] ?? null;

    if (
        !is_array($uploadedFile) ||
        is_array($uploadedFile['name'] ?? null)
    ) {
        jsonResponse(
            422,
            'error',
            'Please upload one encrypted backup file.'
        );
    }

    if (
        !isset($archivePdo) ||
        !$archivePdo instanceof PDO
    ) {
        throw new RuntimeException(
            'Archive database connection unavailable.'
        );
    }

    $encodedKey = $_ENV['BACKUP_ENCRYPTION_KEY'] ?? '';

    $encryptionKey = is_string($encodedKey)
        ? base64_decode($encodedKey, true)
        : false;

    if (
        $encryptionKey === false ||
        strlen($encryptionKey) !== 32
    ) {
        throw new RuntimeException(
            'Backup encryption configuration is invalid.'
        );
    }

    // Initialize services.
    $encryptionService = new BackupEncryptionService(
        $encryptionKey
    );

    $archiveDatabase = $archivePdo
      ->query('SELECT DATABASE()')
      ->fetchColumn();

    if (
        !is_string($archiveDatabase) ||
        $archiveDatabase === ''
    ) {
        throw new RuntimeException(
            'Archive database is not selected.'
        );
    }

    $restoreService = new DatabaseRestoreService(
        $archivePdo,
        $archiveDatabase
    );

    $archiveReadRepo =
    new \App\Repositories\ArchiveRepo\ArchiveReadRepository($archivePdo);

    $validatePayload = require_once __DIR__ . '/../../../app/config/archive-validator.php';

    if (!$validatePayload instanceof Closure) {
        throw new RuntimeException(
            'Archive validator is not configured.'
        );
    }

    $recoveryService = new ArchiveRecoveryService(
        $archivePdo,
        $encryptionService,
        $restoreService,
        $validatePayload,
        $archiveDatabase
    );

    // Execute archive restoration
    $result = $recoveryService->restoreUploadedBackup(
        $uploadedFile,
        $periodId,
        (int) $_SESSION['admin_id']
    );

    jsonResponse(
        200,
        'success',
        'Archived evaluation records restored successfully.',
        $result
    );

} catch (Throwable $e) {
    error_log(
        '[ARCHIVE RECOVERY API] ' . $e->getMessage()
    );

    jsonResponse(
        500,
        'error',
        'Unable to restore archived evaluation records.'
    );
}
