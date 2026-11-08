<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Services\BackupServices\DatabaseExportService;

$root = dirname(__DIR__, 2);
Dotenv\Dotenv::createImmutable($root)->load();
$path = $argv[1] ?? $root . '/storage/private/backups/smart-eval-period-61-20261028-175204.json';
$pdo = null;
$failures = 0;

function result(bool $passed, string $message): void
{
    global $failures;
    if (!$passed) $failures++;
    echo ($passed ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
}
function identifier(string $name): string
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $name)) throw new RuntimeException('Unsupported schema identifier.');
    return '`' . $name . '`';
}
function fingerprints(array $rows): array
{
    $hashes = [];
    foreach ($rows as $row) {
        ksort($row);
        foreach ($row as &$value) {
            if ($value !== null) {
                if (!is_scalar($value)) throw new RuntimeException('Invalid backup row value.');
                $value = (string) $value;
            }
        }
        unset($value);
        $hashes[] = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
    }
    sort($hashes);
    return $hashes;
}

try {
    if (($_ENV['RESTORE_DB_NAME'] ?? '') !== 'dbsmarteval_restore_test') {
        throw new RuntimeException('RESTORE_DB_NAME must be dbsmarteval_restore_test.');
    }
    if (!is_file($path) || !is_readable($path)) throw new RuntimeException('Readable plaintext backup JSON required.');
    $backup = json_decode(file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
    $manifest = $backup['manifest'] ?? [];
    $tables = $backup['tables'] ?? [];
    if (($manifest['format'] ?? '') !== 'smart-eval-period-backup' || ($manifest['version'] ?? null) !== 1) {
        throw new RuntimeException('Unsupported backup format.');
    }
    $periodId = filter_var($manifest['period_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$periodId || $periodId < 1) throw new RuntimeException('Invalid period ID.');
    foreach (DatabaseExportService::TABLES as $table) {
        if (!isset($tables[$table]) || !is_array($tables[$table]) || !array_is_list($tables[$table])) {
            throw new RuntimeException("Missing or invalid table: $table");
        }
        foreach ($tables[$table] as $row) {
            if (!is_array($row) || (string) ($row['period_id'] ?? '') !== (string) $periodId) {
                throw new RuntimeException("Invalid period reference in $table.");
            }
        }
    }
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';port=' . ($_ENV['DB_PORT'] ?? '3306')
            . ';dbname=dbsmarteval_restore_test;charset=utf8mb4',
        $_ENV['DB_USER'], $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
    if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'dbsmarteval_restore_test') {
        throw new RuntimeException('Unexpected database; verification stopped.');
    }
    $pdo->exec("SET time_zone = '+08:00'");
    $pdo->exec('SET TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    echo "Read-only verification: dbsmarteval_restore_test, period $periodId" . PHP_EOL;

    $actualTables = [];
    $total = 0;
    foreach (DatabaseExportService::TABLES as $table) {
        $stmt = $pdo->prepare('SELECT * FROM ' . identifier($table) . ' WHERE period_id = ?');
        $stmt->execute([$periodId]);
        $rows = $stmt->fetchAll();
        $actualTables[$table] = $rows;
        $expected = $manifest['record_counts'][$table] ?? null;
        result(is_int($expected) && $expected === count($tables[$table]) && $expected === count($rows),
            "$table counts: manifest=" . (is_int($expected) ? $expected : 'invalid') . ', database=' . count($rows));
        result(fingerprints($tables[$table]) === fingerprints($rows), "$table complete row values match backup");
        $total += count($rows);
    }

    $stmt = $pdo->query("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA,
        REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, ORDINAL_POSITION
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION");
    $constraints = [];
    foreach ($stmt->fetchAll() as $fk) {
        if (in_array($fk['TABLE_NAME'], DatabaseExportService::TABLES, true)) {
            $constraints[$fk['TABLE_NAME'] . '.' . $fk['CONSTRAINT_NAME']][] = $fk;
        }
    }
    result(count($constraints) > 0, 'Foreign-key constraints exist in the test schema');
    foreach ($constraints as $name => $columns) {
        $first = $columns[0];
        if ($first['REFERENCED_TABLE_SCHEMA'] !== 'dbsmarteval_restore_test') {
            result(false, "$name references a different database");
            continue;
        }
        $join = [];
        $notNull = [];
        foreach ($columns as $fk) {
            $child = 'c.' . identifier($fk['COLUMN_NAME']);
            $parent = 'p.' . identifier($fk['REFERENCED_COLUMN_NAME']);
            $join[] = "$child = $parent";
            $notNull[] = "$child IS NOT NULL";
        }
        $sql = 'SELECT COUNT(*) FROM ' . identifier($first['TABLE_NAME']) . ' c LEFT JOIN '
            . identifier($first['REFERENCED_TABLE_NAME']) . ' p ON ' . implode(' AND ', $join)
            . ' WHERE c.period_id = ? AND ' . implode(' AND ', $notNull)
            . ' AND p.' . identifier($first['REFERENCED_COLUMN_NAME']) . ' IS NULL';
        $check = $pdo->prepare($sql);
        $check->execute([$periodId]);
        result((int) $check->fetchColumn() === 0, "$name has no orphaned records");
    }
    // Compare report inputs independently, without loading any live-database report code.
    $summary = static function (array $data): array {
        $submitted = [];
        foreach ($data['evaluation_status'] as $row) {
            if ((int) $row['is_submitted'] === 1) $submitted[(string) $row['eval_id']] = true;
        }
        $scores = [];
        foreach ($data['evaluation_answers'] as $row) {
            if (isset($submitted[(string) $row['eval_id']])) $scores[] = (float) $row['score'];
        }
        return [count($submitted), count($scores), $scores ? round(array_sum($scores) / count($scores), 6) : null];
    };
    result($summary($tables) === $summary($actualTables), 'Submitted-evaluation count and answer-weighted mean match backup');
    $pdo->rollBack();
    echo "Verified $total database records. Failures: $failures. No database records changed." . PHP_EOL;
    exit($failures ? 1 : 0);
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    // Do not print PDO connection details or record contents.
    fwrite(STDERR, $error instanceof PDOException
        ? 'Verification database query failed (SQLSTATE ' . $error->getCode() . ').' . PHP_EOL
        : 'Verification stopped: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
