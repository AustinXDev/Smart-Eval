<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class ResetStatement extends PDOStatement
{
    public function __construct(private ResetPDO $database, private string $query) {}
    public function execute(?array $params = null): bool {
        $this->database->queries[] = $this->query;
        if (str_contains($this->query, 'SET password_hash')) $this->database->passwordWrites++;
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed {
        return $this->database->valid ? ['student_id' => 'owner', 'admin_id' => 1] : false;
    }
    public function rowCount(): int {
        return str_contains($this->query, 'SET used') ? $this->database->consumeRows : $this->database->passwordRows;
    }
}
class ResetPDO extends PDO
{
    public bool $valid = true;
    public int $consumeRows = 1;
    public int $passwordRows = 1;
    public int $passwordWrites = 0;
    public bool $transaction = false;
    public bool $committed = false;
    public bool $rolledBack = false;
    public array $queries = [];
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new ResetStatement($this, $query); }
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function inTransaction(): bool { return $this->transaction; }
    public function commit(): bool { $this->committed = true; $this->transaction = false; return true; }
    public function rollBack(): bool { $this->rolledBack = true; $this->transaction = false; return true; }
}
foreach ([\App\Repositories\StudentRepo\ResetPasswordRepository::class => 'password_resets',
    \App\Repositories\AdminRepo\AdminResetPasswordRepository::class => 'admin_password_resets'] as $class => $table) {
    foreach (['valid', 'expired', 'already_consumed', 'missing_account'] as $scenario) {
        $pdo = new ResetPDO();
        if ($scenario === 'expired') $pdo->valid = false;
        if ($scenario === 'already_consumed') $pdo->consumeRows = 0;
        if ($scenario === 'missing_account') $pdo->passwordRows = 0;
        $result = (new $class($pdo))->consumeAndReset('hashed-token', 'hashed-password');
        if ($result !== ($scenario === 'valid') || $pdo->transaction) throw new RuntimeException('Reset result failed: ' . $scenario);
        if ($scenario === 'valid' && (!$pdo->committed || $pdo->passwordWrites !== 1)) throw new RuntimeException('Reset commit failed');
        if ($scenario !== 'valid' && (!$pdo->rolledBack || $pdo->committed)) throw new RuntimeException('Reset rollback failed');
        if (in_array($scenario, ['expired', 'already_consumed'], true) && $pdo->passwordWrites !== 0) throw new RuntimeException('Invalid token changed password');
        $query = preg_replace('/\s+/', ' ', implode(' ', $pdo->queries));
        if (!str_contains($query, 'FROM ' . $table) || !str_contains($query, 'FOR UPDATE') || !str_contains($query, 'expires_at > NOW()')) throw new RuntimeException('Reset token scope/locking failed');
        if ($table === 'admin_password_resets' && preg_match('/(?:FROM|UPDATE) password_resets\b/', $query)) throw new RuntimeException('Administrator token used student table');
        echo 'PASS ' . $table . ' ' . $scenario . PHP_EOL;
    }
}
