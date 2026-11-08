<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\ArchiveServices\ArchiveRecoveryService;
use RuntimeException;
use Throwable;

final class ArchiveRecoveryController
{
    public function __construct(
        private ArchiveRecoveryService $recoveryService
    ) {
    }


    public function restore(): void
    {

        header('Content-Type: application/json; charset=utf-8');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                throw new RuntimeException(
                    'Method not allowed.'
                );
            }

            // Check authentication and authorization.
            if (
                empty($_SESSION['admin_id']) ||
                $_SESSION['admin_authenticated'] !== true
            ) {

                http_response_code(401);
                throw new RuntimeException(
                    'Administrator authentication required.'
                );
            }

            // Authorization check: only super admins can restore backups.
            if ($_SESSION['admin_role'] !== 'super_admin') {

                http_response_code(403);
                throw new RuntimeException(
                    'Only Super Admin can restore backups.'
                );

            }

            // validate period id
            $periodId = filter_var(
                $_POST['period_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($periodId === false || $periodId <= 0) {
                http_response_code(422);
                throw new RuntimeException(
                    'Select a valid evaluation period.'
                );
            }

            // Validate uploaded file.
            $uploadedFile = $_FILES['backup_file'] ?? null;

            if (
                !is_array($uploadedFile) ||
                is_array($uploadedFile['name'] ?? null)
            ) {
                http_response_code(422);
                throw new RuntimeException(
                    'Please upload one encrypted backup file.'
                );
            }

            $adminId = (int) $_SESSION['admin_id'];

            // Execute recovery.
            $result = $this->recoveryService
                ->restoreUploadedBackup(
                    $uploadedFile,
                    $periodId,
                    $adminId
                );

            http_response_code(200);

            echo json_encode([
                'status' => 'success',
                'message' => 'Archive recovery completed successfully.',
                'data' => $result
            ], JSON_THROW_ON_ERROR);

        } catch (Throwable $e) {
            if (http_response_code() === 200) {
                http_response_code(500);
            }

            error_log(
                '[ARCHIVE RECOVERY] ' . $e->getMessage()
            );

            echo json_encode([
                'status' => 'error',
                'message' => http_response_code() === 500
                    ? 'Unable to complete archive recovery.'
                    : $e->getMessage()
            ]);
        }

    }
}
