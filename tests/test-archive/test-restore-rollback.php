<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Services\BackupServices\DatabaseExportService;
use App\Services\BackupServices\DatabaseRestoreService;

/**
 * Real database transactions, restricted to the restore-test database.
 * Commit is blocked as a second safeguard even if the expected failure disappears.
 */
final class RollbackTestPDO extends PDO
{
    public int $testPeriod = 0;
    public array $rowsBeforeRollback = [];
    public bool $rollbackObserved = false;

    public function commit(): bool
    {
        throw new RuntimeException('Unexpected commit blocked by the rollback test.');
    }

    public function rollBack(): bool
    {
        try {
            foreach (DatabaseExportService::TABLES as $table) {
                $stmt = $this->prepare("SELECT COUNT(*) FROM `$table` WHERE period_id = ?");
                $stmt->execute([$this->testPeriod]);
                $this->rowsBeforeRollback[$table] = (int) $stmt->fetchColumn();
            }
        } finally {
            $result = parent::rollBack();
            $this->rollbackObserved = $result;
        }
        return $result;
    }
}

function snapshot(PDO $pdo): array
{
    $snapshot = [];
    foreach (DatabaseExportService::TABLES as $table) {
        $rows = [];
        $stmt = $pdo->query("SELECT * FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ksort($row);
            $rows[] = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
        }
        sort($rows);
        $snapshot[$table] = $rows;
    }
    return $snapshot;
}

function requirePass(bool $condition, string $description): void
{
    if (!$condition) throw new RuntimeException($description);
    echo 'PASS ' . $description . PHP_EOL;
}

$pdo = null;
$temporaryPath = null;
$exitCode = 0;
try {
    $root = dirname(__DIR__, 2);
    Dotenv\Dotenv::createImmutable($root)->load();
    if (($_ENV['RESTORE_DB_NAME'] ?? '') !== 'dbsmarteval_restore_test') {
        throw new RuntimeException('RESTORE_DB_NAME must be dbsmarteval_restore_test.');
    }
    $pdo = new RollbackTestPDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';port=' . ($_ENV['DB_PORT'] ?? '3306')
            . ';dbname=dbsmarteval_restore_test;charset=utf8mb4',
        $_ENV['DB_USER'], $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
    requirePass($pdo->query('SELECT DATABASE()')->fetchColumn() === 'dbsmarteval_restore_test', 'Target is the restore-test database');
    requirePass((int) $pdo->query('SELECT @@SESSION.foreign_key_checks')->fetchColumn() === 1, 'Foreign-key checks are enabled');
    $pdo->exec("SET time_zone = '+08:00'");
    $engines = $pdo->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach (DatabaseExportService::TABLES as $table) {
        if (strtoupper($engines[$table] ?? '') !== 'INNODB') throw new RuntimeException("Transactional InnoDB table required: $table");
    }
    requirePass(true, 'All 11 destination tables use InnoDB');
    $fk = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'question_snapshots'
        AND COLUMN_NAME = 'set_id' AND REFERENCED_TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME = 'question_set_snapshots' AND REFERENCED_COLUMN_NAME = 'set_id'")->fetchColumn();
    requirePass((int) $fk > 0, 'Question-to-question-set foreign key exists within the test database');

    // Never reuse an existing period, and never edit or delete any stored record.
    $periodId = random_int(1800000000, 2000000000);
    foreach (DatabaseExportService::TABLES as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE period_id = ?");
        $stmt->execute([$periodId]);
        if ((int) $stmt->fetchColumn() !== 0) throw new RuntimeException('Synthetic ID is already used; rerun the test.');
    }
    $pdo->testPeriod = $periodId;
    $before = snapshot($pdo);
    $tables = array_fill_keys(DatabaseExportService::TABLES, []);
    $tables['evaluation_periods'] = [[
        'period_id' => $periodId, 'academic_year' => '2098-2099', 'semester' => '1st Semester',
        'target_dept' => 'college', 'set_id' => 1,
        'start_date' => '2098-01-01 00:00:00', 'end_date' => '2098-06-01 00:00:00',
        'is_active' => 0, 'is_closed' => 1,
    ]];
    $tables['question_set_snapshots'] = [[
        'period_id' => $periodId, 'set_id' => 1, 'set_name' => 'Synthetic rollback test',
    ]];
    // This is inserted after the valid period and question set. Set 2 does not exist.
    $tables['question_snapshots'] = [[
        'period_id' => $periodId, 'question_id' => 1, 'set_id' => 2,
        'question_text' => 'Deliberate invalid reference for rollback testing',
        'category' => 'Test', 'is_active' => 1,
    ]];
    $payload = [
        'manifest' => [
            'format' => 'smart-eval-period-backup', 'version' => 1,
            'period_id' => $periodId, 'exported_at' => gmdate(DATE_ATOM),
            'record_counts' => array_map('count', $tables),
        ],
        'tables' => $tables,
    ];
    $temporaryPath = tempnam(sys_get_temp_dir(), 'smarteval_rollback_');
    if ($temporaryPath === false) throw new RuntimeException('Cannot create temporary test fixture.');
    chmod($temporaryPath, 0600);
    $json = json_encode($payload, JSON_THROW_ON_ERROR);
    if (file_put_contents($temporaryPath, $json, LOCK_EX) !== strlen($json)) {
        throw new RuntimeException('Cannot write temporary test fixture.');
    }

    $failure = null;
    try {
        (new DatabaseRestoreService($pdo))->restoreFromJson($temporaryPath);
    } catch (Throwable $error) {
        $failure = $error;
    }
    // Save the observed state before fallback cleanup; fallback must not masquerade as a pass.
    $serviceRolledBack = $pdo->rollbackObserved && !$pdo->inTransaction();
    if ($pdo->inTransaction()) $pdo->rollBack();
    requirePass(
        $failure instanceof PDOException && (int) ($failure->errorInfo[1] ?? 0) === 1452,
        'Restore failed on the deliberate foreign-key violation (1452)'
    );
    requirePass(
        ($pdo->rowsBeforeRollback['evaluation_periods'] ?? 0) === 1
        && ($pdo->rowsBeforeRollback['question_set_snapshots'] ?? 0) === 1,
        'Two valid parent records existed inside the transaction before rollback'
    );
    requirePass($serviceRolledBack, 'DatabaseRestoreService rolled back and closed its transaction');

    foreach (DatabaseExportService::TABLES as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE period_id = ?");
        $stmt->execute([$periodId]);
        requirePass((int) $stmt->fetchColumn() === 0, "$table has no remaining synthetic records");
    }
    requirePass(snapshot($pdo) === $before, 'All existing records across the 11 tables are unchanged, including period 61');
    echo "Rollback test passed. No records committed; no records deleted." . PHP_EOL;
} catch (Throwable $error) {
    $exitCode = 1;
    fwrite(STDERR, $error instanceof PDOException
        ? 'FAIL Database operation failed (SQLSTATE ' . $error->getCode() . ').' . PHP_EOL
        : 'FAIL ' . $error->getMessage() . PHP_EOL);
} finally {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    if (is_string($temporaryPath) && is_file($temporaryPath) && !unlink($temporaryPath)) {
        fwrite(STDERR, 'FAIL Could not remove the temporary synthetic fixture.' . PHP_EOL);
        $exitCode = 1;
    }
}
exit($exitCode);
