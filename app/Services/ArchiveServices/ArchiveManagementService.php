<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use App\Repositories\AdminRepo\AdminRepository;
use App\Services\BackupServices\{ArchiveBackupService, BackupEncryptionService, BackupPreviewService, DatabaseExportService, GoogleDriveService, GoogleTokenStorage};
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class ArchiveManagementService
{
    public function __construct(
        private ArchiveReadRepository $archive,
        private AdminRepository $admins,
        private PDO $archivePdo
    ) {
    }

    public function overview(): array
    {
        return ['metrics' => $this->archive->managementOverview(), 'filters' => $this->archive->managementOptions()];
    }

    public function listing(array $input): array
    {
        [$start, $length, $draw] = $this->pagination($input);
        $filters = $this->filters($input);
        return ['draw' => $draw] + $this->archive->managementList($filters, $start, $length);
    }

    public function details(mixed $id): array
    {
        $record = $this->archive->managementDetails(self::positiveId($id));
        if (!$record) throw new InvalidArgumentException('Archived period not found.');
        return $record;
    }

    public function logs(array $input): array
    {
        [$start, $length, $draw] = $this->pagination($input);
        $filters = $this->filters($input);
        $filters['period_id'] = self::text($input, 'period_id', 20);
        if ($filters['period_id'] !== '') self::positiveId($filters['period_id']);
        $result = $this->archive->recoveryLogs($filters, $start, $length);
        $names = [];
        foreach ($result['data'] as &$row) {
            $adminId = (int) $row['admin_id'];
            if (!array_key_exists($adminId, $names)) $names[$adminId] = $this->admins->findById($adminId)?->username;
            $row['administrator'] = $names[$adminId];
            $row['result'] = $row['status'];
        }
        unset($row);
        return ['draw' => $draw] + $result;
    }

    public function logDetails(mixed $id): array
    {
        $record = $this->archive->recoveryLogDetails(self::positiveId($id));
        if (!$record) throw new InvalidArgumentException('Recovery log entry not found.');
        $record['administrator'] = $this->admins->findById((int) $record['admin_id'])?->username;
        return $record;
    }

    public function verify(mixed $id): array
    {
        $periodId = self::positiveId($id);
        $this->details($periodId);
        $service = new ArchiveBackupService(
            $this->archivePdo,
            new DatabaseExportService($this->archivePdo, sys_get_temp_dir()),
            $this->encryption(),
            new GoogleDriveService(new GoogleTokenStorage())
        );
        // This only downloads/checks the stored encrypted file; it does not run the worker
        // or change archive/cleanup statuses.
        return $service->verifyExistingBackup($periodId);
    }

    public function preview(array $file): array
    {
        return (new BackupPreviewService($this->archive, $this->encryption()))->previewUpload($file);
    }

    private function encryption(): BackupEncryptionService
    {
        $encoded = $_ENV['BACKUP_ENCRYPTION_KEY'] ?? '';
        $key = is_string($encoded) ? base64_decode($encoded, true) : false;
        if ($key === false || strlen($key) !== 32) throw new RuntimeException('Backup encryption is not configured.');
        return new BackupEncryptionService($key);
    }

    public static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) throw new InvalidArgumentException('Invalid record identifier.');
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) throw new InvalidArgumentException('Invalid record identifier.');
        return $id;
    }

    private function pagination(array $input): array
    {
        $values = [];
        foreach (['start' => 0, 'length' => 10, 'draw' => 0] as $key => $default) {
            $raw = $input[$key] ?? $default;
            if ((!is_string($raw) && !is_int($raw)) || filter_var($raw, FILTER_VALIDATE_INT) === false) {
                throw new InvalidArgumentException('Invalid pagination.');
            }
            $values[$key] = (int) $raw;
        }
        if ($values['start'] < 0 || $values['start'] > 1000000 || $values['draw'] < 0 || !in_array($values['length'], [5, 10, 25, 50], true)) {
            throw new InvalidArgumentException('Invalid pagination.');
        }
        return [$values['start'], $values['length'], $values['draw']];
    }

    private function filters(array $input): array
    {
        $filters = [];
        foreach (['department', 'year', 'semester', 'backup_status', 'search'] as $key) $filters[$key] = self::text($input, $key, $key === 'search' ? 100 : 30);
        if (!in_array($filters['department'], ['', 'college', 'shs'], true)
            || !in_array($filters['backup_status'], ['', 'pending', 'processing', 'verified', 'failed', 'missing'], true)) {
            throw new InvalidArgumentException('Invalid archive filter.');
        }
        return $filters;
    }

    private static function text(array $input, string $key, int $max): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value) || strlen($value) > $max) throw new InvalidArgumentException('Invalid filter value.');
        return trim($value);
    }
}
