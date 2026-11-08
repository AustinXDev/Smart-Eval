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

    private array $columnCache = [];

    public function __construct(
        private PDO $restorePdo,
        private string $allowedDatabase = 'dbsmarteval_restore_test'
    ) {
        if (!in_array(
            $allowedDatabase,
            [
                'dbsmarteval_restore_test',
                'dbsmarteval_archive'
            ],
            true
        )) {
            throw new RuntimeException(
                'Invalid restoration destination.'
            );
        }

        $this->restorePdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    /**
     * Standalone restoration.
     *
     * Used by existing test scripts.
     * Owns the transaction and advisory lock.
     */
    public function restoreFromJson(
        string $jsonPath
    ): array {
        $this->assertDatabase();

        if ($this->restorePdo->inTransaction()) {
            throw new RuntimeException(
                'Cannot restore while a transaction is active.'
            );
        }

        $backup = $this->loadAndValidateBackup($jsonPath);

        $periodId = $backup['period_id'];
        $lockName = $this->lockName($periodId);

        $this->acquireLock($lockName);

        try {
            $this->restorePdo->beginTransaction();

            try {
                $result = $this->restoreValidatedBackup(
                    $backup
                );

                $this->restorePdo->commit();

                return $result;

            } catch (Throwable $e) {
                if ($this->restorePdo->inTransaction()) {
                    $this->restorePdo->rollBack();
                }

                throw $e;
            }

        } finally {
            $this->releaseLock($lockName);
        }
    }

    /**
     * Production restoration.
     *
     * The caller must:
     * - Acquire the same period advisory lock.
     * - Begin a transaction on the same PDO.
     * - Commit or roll back the transaction.
     *
     * This method never commits or rolls back.
     */
    public function restoreWithinTransaction(
        string $jsonPath
    ): array {
        $this->assertDatabase();

        if (!$this->restorePdo->inTransaction()) {
            throw new RuntimeException(
                'An active restoration transaction is required.'
            );
        }

        $backup = $this->loadAndValidateBackup($jsonPath);

        $periodId = $backup['period_id'];
        $lockName = $this->lockName($periodId);

        $stmt = $this->restorePdo->prepare(
            'SELECT IS_USED_LOCK(?)'
        );

        $stmt->execute([$lockName]);

        $ownerConnection = $stmt->fetchColumn();

        $connectionId = $this->restorePdo
            ->query('SELECT CONNECTION_ID()')
            ->fetchColumn();

        if (
            $ownerConnection === false ||
            $ownerConnection === null ||
            (string) $ownerConnection !== (string) $connectionId
        ) {
            throw new RuntimeException(
                'The current database connection does not '
                . 'own the required archive period lock.'
            );
        }

        return $this->restoreValidatedBackup($backup);
    }

    /**
     * Read and validate a backup before insertion.
     */
    private function loadAndValidateBackup(
        string $jsonPath
    ): array {
        if (
            !is_file($jsonPath) ||
            !is_readable($jsonPath)
        ) {
            throw new RuntimeException(
                'Backup JSON file not found or unreadable.'
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

        if (!is_array($backup)) {
            throw new RuntimeException(
                'Invalid backup JSON structure.'
            );
        }

        $manifest = $backup['manifest'] ?? null;
        $tables = $backup['tables'] ?? null;

        $periodId = $this->validateBackup(
            $manifest,
            $tables
        );

        return [
            'period_id' => $periodId,
            'manifest' => $manifest,
            'tables' => $tables,
        ];
    }

    /**
     * Restore validated evaluation records.
     *
     * Requires an existing transaction.
     * Does not commit or roll back.
     */
    private function restoreValidatedBackup(
        array $backup
    ): array {
        if (!$this->restorePdo->inTransaction()) {
            throw new RuntimeException(
                'Restoration requires an active transaction.'
            );
        }

        $periodId = $backup['period_id'];
        $manifest = $backup['manifest'];
        $tables = $backup['tables'];

        $this->assertPeriodNotExist($periodId);

        $restoredCounts = [];

        foreach (self::TABLES as $table) {
            $restoredCounts[$table] = 0;

            foreach ($tables[$table] as $row) {
                $this->insertRow($table, $row);
                $restoredCounts[$table]++;
            }
        }

        $this->verifyRestoredCounts(
            $periodId,
            $manifest['record_counts']
        );

        return [
            'period_id' => $periodId,
            'status' => 'restored',
            'record_counts' => $restoredCounts,
        ];
    }

    /**
     * Ensure the destination is the configured database.
     */
    private function assertDatabase(): void
    {
        $database = $this->restorePdo
            ->query('SELECT DATABASE()')
            ->fetchColumn();

        if ($database !== $this->allowedDatabase) {
            throw new RuntimeException(
                'Restoration database mismatch.'
            );
        }
    }

    /**
     * Validate backup manifest and table contents.
     */
    private function validateBackup(
        mixed $manifest,
        mixed $tables
    ): int {
        if (
            !is_array($manifest) ||
            !is_array($tables) ||
            ($manifest['format'] ?? null)
                !== 'smart-eval-period-backup' ||
            ($manifest['version'] ?? null) !== 1 ||
            !isset($manifest['record_counts']) ||
            !is_array($manifest['record_counts'])
        ) {
            throw new RuntimeException(
                'Invalid Smart-Eval backup format.'
            );
        }

        $periodId = filter_var(
            $manifest['period_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $periodId === false ||
            $periodId <= 0
        ) {
            throw new RuntimeException(
                'Invalid backup period ID.'
            );
        }

        if (
            array_diff(self::TABLES, array_keys($tables)) ||
            array_diff(array_keys($tables), self::TABLES)
        ) {
            throw new RuntimeException(
                'Backup contains missing or unsupported tables.'
            );
        }

        foreach (self::TABLES as $table) {
            if (
                !is_array($tables[$table]) ||
                !array_is_list($tables[$table])
            ) {
                throw new RuntimeException(
                    "Invalid backup table: {$table}"
                );
            }

            $expected =
                $manifest['record_counts'][$table] ?? null;

            if (
                !is_int($expected) ||
                $expected < 0 ||
                $expected !== count($tables[$table])
            ) {
                throw new RuntimeException(
                    "Invalid record count for {$table}"
                );
            }

            foreach ($tables[$table] as $row) {
                if (
                    !is_array($row) ||
                    $row === [] ||
                    !array_key_exists('period_id', $row) ||
                    (string) $row['period_id']
                        !== (string) $periodId
                ) {
                    throw new RuntimeException(
                        "Invalid period reference in {$table}"
                    );
                }
            }
        }

        if (count($tables['evaluation_periods']) !== 1) {
            throw new RuntimeException(
                'Backup must contain exactly one evaluation period.'
            );
        }

        return $periodId;
    }

    /**
     * Reject an already restored period.
     */
    private function assertPeriodNotExist(
        int $periodId
    ): void {
        $stmt = $this->restorePdo->prepare(
            'SELECT 1
             FROM evaluation_periods
             WHERE period_id = :period_id
             LIMIT 1'
        );

        $stmt->execute([
            'period_id' => $periodId
        ]);

        if ($stmt->fetchColumn() !== false) {
            throw new RuntimeException(
                'This evaluation period already exists '
                . 'in the destination database.'
            );
        }
    }

    /**
     * Insert one row into an allowed archive table.
     */
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

        $columns = array_keys($row);

        if (!isset($this->columnCache[$table])) {
            $stmt = $this->restorePdo->query(
                "SHOW COLUMNS FROM `{$table}`"
            );

            $this->columnCache[$table] =
                $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $allowedColumns = $this->columnCache[$table];

        foreach ($columns as $column) {
            if (
                !is_string($column) ||
                !in_array($column, $allowedColumns, true)
            ) {
                throw new RuntimeException(
                    "Unexpected column in {$table}."
                );
            }
        }

        $quotedColumns = array_map(
            static fn (string $column): string =>
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

    /**
     * Verify restored database record counts.
     */
    private function verifyRestoredCounts(
        int $periodId,
        array $expectedCounts
    ): void {
        foreach (self::TABLES as $table) {
            $stmt = $this->restorePdo->prepare(
                "SELECT COUNT(*)
                 FROM `{$table}`
                 WHERE period_id = :period_id"
            );

            $stmt->execute([
                'period_id' => $periodId
            ]);

            $actual = (int) $stmt->fetchColumn();
            $expected = $expectedCounts[$table];

            if ($actual !== $expected) {
                throw new RuntimeException(
                    "Restoration verification failed for {$table}. "
                    . "Expected {$expected}, got {$actual}."
                );
            }
        }
    }

    /**
     * Consistent advisory lock name for all
     * operations affecting one archive period.
     */
    private function lockName(int $periodId): string
    {
        return sprintf(
            'smarteval:archive-period:%s:%d',
            $this->allowedDatabase,
            $periodId
        );
    }

    /**
     * Acquire a MySQL advisory lock.
     */
    private function acquireLock(
        string $lockName
    ): void {
        $stmt = $this->restorePdo->prepare(
            'SELECT GET_LOCK(?, 10)'
        );

        $stmt->execute([$lockName]);

        if ((int) $stmt->fetchColumn() !== 1) {
            throw new RuntimeException(
                'Another archive operation is running.'
            );
        }
    }

    /**
     * Release a MySQL advisory lock.
     */
    private function releaseLock(
        string $lockName
    ): void {
        try {
            $stmt = $this->restorePdo->prepare(
                'SELECT RELEASE_LOCK(?)'
            );

            $stmt->execute([$lockName]);

        } catch (Throwable $e) {
            error_log(
                '[DATABASE RESTORE] Lock release failed: '
                . $e->getMessage()
            );
        }
    }
}
