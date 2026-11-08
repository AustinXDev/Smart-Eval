<?php

declare(strict_types=1);

namespace App\Controllers\Archive;

use App\Services\ArchiveServices\ArchiveManagementService;
use App\middleware\ApiSecurity;
use InvalidArgumentException;
use Throwable;

final class ArchiveManagementController
{
    public function __construct(private ArchiveManagementService $service)
    {
    }

    public function handle(string $action): void
    {
        try {
            $data = match ($action) {
                'overview' => $this->service->overview(),
                'list' => $this->service->listing($_GET),
                'details' => $this->service->details($_GET['period_id'] ?? null),
                'logs' => isset($_GET['id'])
                    ? $this->service->logDetails($_GET['id'])
                    : $this->service->logs($_GET),
                'verify' => $this->service->verify($this->jsonBody()['period_id'] ?? null),
                'preview' => $this->service->preview($_FILES['backup'] ?? []),
                default => throw new InvalidArgumentException('Unsupported operation.'),
            };
            if (in_array($action, ['verify', 'preview'], true)) {
                error_log('[Archive management] ' . json_encode([
                    'action' => $action, 'admin_id' => (int) $_SESSION['admin_id'],
                    'period_id' => $data['period_id'] ?? null, 'outcome' => 'success',
                ]));
            }
            // DataTables endpoints retain the application's top-level paging envelope.
            $response = in_array($action, ['list', 'logs'], true) && isset($data['draw'])
                ? ['status' => 'success'] + $data
                : ['status' => 'success', 'data' => $data];
            echo json_encode($response, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (InvalidArgumentException $e) {
            error_log('[Archive management validation] ' . $action . ': ' . $e->getMessage());
            ApiSecurity::fail(422, $e->getMessage());
        } catch (Throwable $e) {
            error_log('[Archive management] ' . $action . ' admin=' . (int) ($_SESSION['admin_id'] ?? 0)
                . ': ' . get_class($e) . ': ' . $e->getMessage());
            ApiSecurity::fail(500, $action === 'verify'
                ? 'Backup verification failed. Availability or checksum could not be confirmed. Check the server logs.'
                : 'Unable to complete this archive request. Please try again or contact the administrator.');
        }
    }

    private function jsonBody(): array
    {
        return json_decode(file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    }
}
