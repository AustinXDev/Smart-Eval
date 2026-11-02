<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use PDO;
use RuntimeException;
use Throwable;

final class DatabaseRestoreService
{
    private const TABLES = [
        'evaluation_periods',
        'question_set_snapshots',
        'student_snapshots',
        'program_snapshots',
        'teacher_snapshots',
        'question_snapshots',
        'evaluation_status',
        'evaluation_answers',
        'participation_history',
        'notification_queue',
        'teacher_notification_queue',
    ];

    public function __construct(
        private PDO $restorePdo
    ) {
    }

    public function restoreFromJson(string $jsonPath): array
    {
        if (!is_file($jsonPath)) {
            throw new RuntimeException(
                'Backup JSON file not found.'
            );
        }

        // Safety: never restore into the live or archive DB.
        $database = $this->restorePdo
            ->query('SELECT DATABASE()')
            ->fetchColumn();

        if ($database !== 'dbsmarteval_restore_test') {
            throw new RuntimeException(
                'Restoration is permitted only in '
                . 'dbsmarteval_restore_test.'
            );
        }

        $json = file_get_contents($jsonPath);

        if ($json === false) {
            throw new RuntimeException(
                'Unable to read backup JSON.'
            );
        }

        $backup = json_decode(
            $json,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $manifest = $backup['manifest'] ?? null;
        $tables = $backup['tables'] ?? null;

        if (
            !is_array($manifest) ||
            !is_array($tables) ||
            ($manifest['format'] ?? '') !==
                'smart-eval-period-backup' ||
            ($manifest['version'] ?? null) !== 1
        ) {
            throw new RuntimeException(
                'Invalid Smart-Eval backup format.'
            );
        }

        $periodId = filter_var(
            $manifest['period_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($periodId === false || $periodId <= 0) {
            throw new RuntimeException(
                'Invalid backup period ID.'
            );
        }

        foreach (self::TABLES as $table) {
            if (
                !isset($tables[$table]) ||
                !is_array($tables[$table])
            ) {
                throw new RuntimeException(
                    "Missing backup table: {$table}"
                );
            }

            $expected = $manifest['record_counts'][$table]
                ?? null;

            if (
                !is_int($expected) ||
                $expected !== count($tables[$table])
            ) {
                throw new RuntimeException(
                    "Invalid record count for {$table}"
                );
            }

            foreach ($tables[$table] as $row) {
                if (
                    !is_array($row) ||
                    !array_key_exists('period_id', $row) ||
                    (string) $row['period_id'] !==
                        (string) $periodId
                ) {
                    throw new RuntimeException(
                        "Invalid period reference in {$table}"
                    );
                }
            }
        }

        $this->restorePdo->beginTransaction();

        try {
            $restoredCounts = [];

            foreach (self::TABLES as $table) {
                $restoredCounts[$table] = 0;

                foreach ($tables[$table] as $row) {
                    $this->insertRow($table, $row);
                    $restoredCounts[$table]++;
                }
            }

            $this->restorePdo->commit();

            return [
                'period_id' => $periodId,
                'status' => 'restored',
                'record_counts' => $restoredCounts,
            ];

        } catch (Throwable $e) {
            if ($this->restorePdo->inTransaction()) {
                $this->restorePdo->rollBack();
            }

            throw $e;
        }
    }

    private function insertRow(
        string $table,
        array $row
    ): void {
        if (!in_array($table, self::TABLES, true)) {
            throw new RuntimeException(
                'Invalid restoration table.'
            );
        }

        if ($row === []) {
            throw new RuntimeException(
                'Cannot insert an empty record.'
            );
        }

        // Validate column names against the destination schema.
        $columns = array_keys($row);

        $stmt = $this->restorePdo->query(
            "SHOW COLUMNS FROM `{$table}`"
        );

        $allowedColumns = $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );

        foreach ($columns as $column) {
            if (!in_array($column, $allowedColumns, true)) {
                throw new RuntimeException(
                    "Unexpected column {$column} in {$table}"
                );
            }
        }

        $quotedColumns = array_map(
            fn (string $column): string =>
                "`{$column}`",
            $columns
        );

        $placeholders = array_fill(
            0,
            count($columns),
            '?'
        );

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        );

        $stmt = $this->restorePdo->prepare($sql);

        $stmt->execute(array_values($row));
    }
}
