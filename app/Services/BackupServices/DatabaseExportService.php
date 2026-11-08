<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use PDO;
use RuntimeException;
use JsonException;
use DateTimeImmutable;
use DateTimeZone;

final class DatabaseExportService
{
    public const TABLES = [
      'evaluation_periods',
      'question_set_snapshots',
      'question_snapshots',
      'teacher_snapshots',
      'student_snapshots',
      'program_snapshots',
      'evaluation_status',
      'evaluation_answers',
      'participation_history',
      'notification_queue',
      'teacher_notification_queue',
    ];

    public function __construct(
        private PDO $archivePdo,
        private string $backupDirectory
    ) {
    }

    public function exportPeriod(
        int $periodId
    ): array {

        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }


        if (!is_dir($this->backupDirectory)) {
            if (!mkdir(
                $this->backupDirectory,
                0700,
                true
            ) && !is_dir($this->backupDirectory)) {
                throw new RuntimeException(
                    'Unable to create backup directory.'
                );
            }
        }


        $this->archivePdo->beginTransaction();


        try {

            $period = $this->getPeriod($periodId);

            if (!$period) {
                throw new RuntimeException(
                    "Archive period {$periodId} not found."
                );
            }

            $tables = [];
            $counts = [];

            foreach (self::TABLES as $table) {

                $rows = $this->getTableRecords(
                    $table,
                    $periodId
                );

                $tables[$table] = $rows;
                $counts[$table] = count($rows);

            }

            $manifest = [
                'format' => 'smart-eval-period-backup',
                'version' => 1,
                'period_id' => $periodId,
                'exported_at' => (
                    new DateTimeImmutable(
                        'now',
                        new DateTimeZone('UTC')
                    )
                )->format(DATE_ATOM),
                'record_counts' => $counts,
            ];

            $payload = [
                'manifest' => $manifest,
                'tables' => $tables,
            ];

            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_THROW_ON_ERROR
            );

            $this->archivePdo->commit();

        } catch (\Throwable $e) {

            if ($this->archivePdo->inTransaction()) {
                $this->archivePdo->rollBack();
            }

            throw $e;

        }

        $filename = sprintf(
            'smart-eval-period-%d-%s.json',
            $periodId,
            gmdate('Ymd-His')
        );

        $path = rtrim(
            $this->backupDirectory,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents(
            $path,
            $json,
            LOCK_EX
        ) === false) {
            throw new RuntimeException(
                'Unable to write backup file.'
            );
        }

        @chmod($path, 0600);

        $checksum = hash_file('sha256', $path);

        if ($checksum === false) {
            throw new RuntimeException(
                'Unable to calculate backup checksum.'
            );
        }

        return [
            'period_id' => $periodId,
            'file_path' => $path,
            'file_name' => $filename,
            'checksum_sha256' => $checksum,
            'manifest' => $manifest,
            'file_size' => filesize($path),
        ];

    }


    private function getPeriod(int $periodId): array|false
    {
        $stmt = $this->archivePdo->prepare(
            'SELECT *
             FROM evaluation_periods
             WHERE period_id = ?'
        );

        $stmt->execute([$periodId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    private function getTableRecords(
        string $table,
        int $periodId
    ): array {
        if (!in_array($table, self::TABLES, true)) {
            throw new RuntimeException(
                'Invalid archive table.'
            );
        }

        $sql = sprintf(
            'SELECT * FROM `%s`
             WHERE period_id = :period_id',
            $table
        );

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
