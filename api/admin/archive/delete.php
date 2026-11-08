<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/init.php';
require_once dirname(__DIR__, 3) . '/app/config/archive-database.php';

use App\Services\ArchiveServices\ArchiveDeletionService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(
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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');

        respond(405, 'error', 'Method not allowed.');
    }

    if (
        empty($_SESSION['admin_id']) ||
        ($_SESSION['admin_authenticated'] ?? false) !== true
    ) {
        respond(401, 'error', 'Authentication required.');
    }

    if (($_SESSION['role'] ?? '') !== 'super admin') {
        respond(403, 'error', 'Super Admin access required.');
    }

    $body = json_decode(
        file_get_contents('php://input') ?: '',
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $periodId = filter_var(
        $body['period_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($periodId === false || $periodId <= 0) {
        respond(422, 'error', 'Invalid evaluation period.');
    }

    if (!isset($archivePdo) || !$archivePdo instanceof PDO) {
        throw new RuntimeException(
            'Archive database connection unavailable.'
        );
    }

    // Verify against your configured archive database name.
    $database = $archivePdo->query('SELECT DATABASE()')
        ->fetchColumn();

    $expectedDatabase = $_ENV['ARCHIVE_DB_NAME'] ?? null;

    if (
        !is_string($expectedDatabase) ||
        $expectedDatabase === '' ||
        $database !== $expectedDatabase
    ) {
        throw new RuntimeException(
            'Archive database configuration mismatch.'
        );
    }

    $service = new ArchiveDeletionService($archivePdo);

    $result = $service->deletePeriod($periodId);

    respond(
        200,
        'success',
        'Archived records deleted. Recovery backup metadata retained.',
        $result
    );

} catch (JsonException $e) {

    respond(400, 'error', 'Invalid JSON request.');

} catch (RuntimeException $e) {

    error_log('[ARCHIVE DELETE] ' . $e->getMessage());

    respond(409, 'error', $e->getMessage());

} catch (Throwable $e) {

    error_log('[ARCHIVE DELETE] ' . $e->getMessage());

    respond(
        500,
        'error',
        'Unable to delete archived records.'
    );

}
