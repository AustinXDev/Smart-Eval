<?php
declare(strict_types=1);
// Standalone fake-transport tests. Never initialize application/production PDO.
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Repositories\NotificationRepositories\NotificationStoreInterface;
use App\Services\NotificationServices\NotificationWorkerService;
use App\Services\Email\DeliveryResult;
use App\Services\Email\TrackedSmtp;
use App\Repositories\EmailRepositories\ProviderQuotaRepository as ProviderQuota;

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

final class MemoryQueue implements NotificationStoreInterface {
    public array $jobs = [];
    public array $transitions = [];
    public bool $loseOwnership = false;
    public bool $failPersistence = false;
    public function __construct(int $count = 1) {
        for ($i = 1; $i <= $count; $i++) $this->jobs[] = [
            'notification_id' => $i, 'claim_token' => 'owned', 'period_id' => 1,
            'student_id' => 'fake', 'type' => 'evaluation_reminder', 'attempt_count' => 1
        ];
    }
    public function recoverStaleJobs(int $leaseMinutes = 10): int { return 0; }
    public function claimBatch(int $limit = 10): array { return array_splice($this->jobs, 0, $limit); }
    private function update(string $state, string $token): bool {
        check($token === 'owned', 'Token was not forwarded');
        $this->transitions[] = $state;
        return !$this->loseOwnership;
    }
    public function markSending(int $id, string $token): bool { return $this->update('sending', $token); }
    public function markSent(int $id, string $token): bool {
        if ($this->failPersistence) throw new RuntimeException('Injected DB failure');
        return $this->update('sent', $token);
    }
    public function markSkipped(int $id, string $token): bool { return $this->update('skipped', $token); }
    public function markUncertain(int $id, string $token): bool { return $this->update('uncertain', $token); }
    public function markFailed(int $id, string $token, int $attemptCount, string $error, bool $terminal = false): bool {
        return $this->update($terminal ? 'permanent' : 'retryable', $token);
    }
    public function defer(int $id, string $token, int $seconds): bool { return $this->update('deferred', $token); }
}
function worker(MemoryQueue $queue, Closure $send, bool $eligible = true): NotificationWorkerService {
    return new NotificationWorkerService($queue, fn() => true, fn() => $eligible, $send);
}

$tests = [];
$tests['accepted is persisted'] = function() {
    $q = new MemoryQueue();
    $r = worker($q, fn() => new DeliveryResult('accepted'))->run();
    check($r['sent'] === 1 && $q->transitions === ['sending', 'sent'], 'Wrong acceptance handling');
};
$tests['eligibility skips SMTP'] = function() {
    $q = new MemoryQueue();
    worker($q, function() { throw new RuntimeException('SMTP must not run'); }, false)->run();
    check($q->transitions === ['skipped'], 'Ineligible job recorded as sent');
};
$tests['lost ownership prevents SMTP'] = function() {
    $q = new MemoryQueue(); $q->loseOwnership = true;
    $sent = 0;
    $r = worker($q, function() use (&$sent) { $sent++; return new DeliveryResult('accepted'); })->run();
    check($sent === 0 && $r['ownership_lost'] === 1, 'Stale worker sent a message');
};
$tests['DB failure after acceptance never retries transport'] = function() {
    $q = new MemoryQueue(2); $q->failPersistence = true;
    $sent = 0;
    $r = worker($q, function() use (&$sent) { $sent++; return new DeliveryResult('accepted'); })->run();
    check($sent === 1 && $r['uncertain'] === 1 && $q->transitions === ['sending'], 'DB error entered retry path');
};
$tests['unexpected transport exception is uncertain'] = function() {
    $q = new MemoryQueue();
    worker($q, function() { throw new RuntimeException('Disconnected after DATA'); })->run();
    check($q->transitions === ['sending', 'uncertain'], 'Ambiguous outcome was retried');
};
$tests['permanent recipient rejection is terminal'] = function() {
    $q = new MemoryQueue();
    worker($q, fn() => new DeliveryResult('permanent', 'recipient'))->run();
    check($q->transitions === ['sending', 'permanent'], 'Permanent failure became retryable');
};
$tests['temporary rejection is retryable'] = function() {
    $q = new MemoryQueue();
    worker($q, fn() => new DeliveryResult('retryable'))->run();
    check($q->transitions === ['sending', 'retryable'], 'Temporary failure became permanent');
};
$tests['quota deferral stops backlog processing'] = function() {
    $q = new MemoryQueue(10);
    $r = worker($q, fn() => new DeliveryResult('deferred', 'quota', 60))->run(10);
    check($r['claimed'] === 1 && $r['deferred'] === 1 && count($q->jobs) === 9, 'Quota block consumed backlog');
};
$tests['deadline prevents claiming'] = function() {
    $q = new MemoryQueue();
    $r = worker($q, fn() => new DeliveryResult('accepted'))->run(10, 1, 2);
    check($r['claimed'] === 0 && count($q->jobs) === 1, 'Claimed near deadline');
};
$tests['maximum jobs respected'] = function() {
    $q = new MemoryQueue(12);
    $r = worker($q, fn() => new DeliveryResult('accepted'))->run(3);
    check($r['sent'] === 3 && count($q->jobs) === 9, 'Job bound exceeded');
};

class TranscriptSmtp extends TrackedSmtp {
    public array $replies = [];
    public function connected() { return true; }
    public function client_send($data, $command = '') { return strlen($data); }
    protected function get_lines() { return array_shift($this->replies) ?? ''; }
}
$tests['SMTP DATA acceptance tracked before reset'] = function() {
    $smtp = new TranscriptSmtp(); $smtp->replies = ["354 send data\r\n", "250 accepted\r\n"];
    check($smtp->data("Subject: fake\r\n\r\nbody") && $smtp->accepted, 'Acceptance missing');
    $smtp->resetOutcome();
    check(!$smtp->accepted && !$smtp->dataStarted, 'Previous outcome leaked');
};
$tests['SMTP missing final reply remains uncertain'] = function() {
    $smtp = new TranscriptSmtp(); $smtp->replies = ["354 send data\r\n", ''];
    check(!$smtp->data("Subject: fake\r\n\r\nbody") && $smtp->dataStarted && !$smtp->accepted && $smtp->failedCode === 0, 'Uncertainty missing');
};
$tests['SMTP final rejection captured'] = function() {
    $smtp = new TranscriptSmtp(); $smtp->replies = ["354 send data\r\n", "451 try later\r\n"];
    check(!$smtp->data("Subject: fake\r\n\r\nbody") && $smtp->failedCode === 451, 'Rejection missing');
};

// SQL execution double verifies reservation decisions and transaction cleanup.
class QuotaStatement extends PDOStatement {
    public function __construct(private QuotaPdo $db, private string $sql) {}
    public function execute(?array $params = null): bool {
        if ($this->db->injectFailure) throw new RuntimeException('Injected persistence failure');
        if (str_starts_with($this->sql, 'INSERT INTO email_provider_reservations')) $this->db->reserved++;
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed {
        return str_contains($this->sql, 'FOR UPDATE')
            ? ['now_ts' => 1000, 'cooldown_ts' => $this->db->cooldown, 'bulk_ts' => $this->db->bulk]
            : ['daily_count' => $this->db->daily, 'minute_count' => $this->db->minute];
    }
}
class QuotaPdo extends PDO {
    public int $daily = 0, $minute = 0, $cooldown = 0, $bulk = 0, $reserved = 0;
    public bool $transaction = false, $injectFailure = false;
    public function __construct() {}
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function inTransaction(): bool { return $this->transaction; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
    public function prepare(string $query, array $options = []): PDOStatement|false { return new QuotaStatement($this, $query); }
}
function quota(QuotaPdo $db): ProviderQuota {
    return new ProviderQuota($db, 'fake', ['MAIL_DAILY_LIMIT' => 100, 'MAIL_MINUTE_LIMIT' => 10, 'MAIL_AUTH_RESERVE' => 20]);
}
$tests['bulk reserve preserves auth capacity'] = function() {
    $db = new QuotaPdo(); $db->daily = 80;
    check(quota($db)->reserve('bulk') > 0 && $db->reserved === 0, 'Bulk used reserved capacity');
    check(quota($db)->reserve('auth') === 0 && $db->reserved === 1 && !$db->transaction, 'Auth reserve unavailable');
};
$tests['minute limit blocks both paths'] = function() {
    $db = new QuotaPdo(); $db->minute = 10;
    check(quota($db)->reserve('auth') >= 60 && $db->reserved === 0, 'Minute cap exceeded');
};
$tests['bulk pacing does not delay auth'] = function() {
    $db = new QuotaPdo(); $db->bulk = 1010;
    check(quota($db)->reserve('bulk') === 10 && quota($db)->reserve('auth') === 0, 'Auth inherited bulk delay');
};
$tests['quota failure rolls back'] = function() {
    $db = new QuotaPdo(); $db->injectFailure = true;
    try { quota($db)->reserve('auth'); throw new LogicException('Expected failure'); }
    catch (RuntimeException $expected) { check(!$db->transaction && $db->reserved === 0, 'Reservation failed open'); }
};

class TransitionStatement extends PDOStatement {
    public function __construct(private TransitionPdo $db) {}
    public function execute(?array $params = null): bool { $this->db->parameters = $params ?? []; return true; }
    public function rowCount(): int { return $this->db->affected; }
}
class TransitionPdo extends PDO {
    public string $sql = '';
    public array $parameters = [];
    public int $affected = 1;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false {
        $this->sql = $query;
        return new TransitionStatement($this);
    }
}
$tests['every repository worker transition checks token ownership'] = function() {
    $db = new TransitionPdo();
    $repo = new App\Repositories\NotificationRepositories\NotificationRepository($db);
    $transitions = [
        fn() => $repo->markSending(5, 'owner'),
        fn() => $repo->markSent(5, 'owner'),
        fn() => $repo->markSkipped(5, 'owner'),
        fn() => $repo->markUncertain(5, 'owner'),
        fn() => $repo->markFailed(5, 'owner', 1, 'safe'),
        fn() => $repo->markFailed(5, 'owner', 3, 'safe'),
        fn() => $repo->defer(5, 'owner', 60),
    ];
    foreach ($transitions as $transition) {
        check($transition(), 'Owned transition failed');
        check(str_contains($db->sql, 'claim_token = ?') && str_contains($db->sql, "status = 'processing'"), 'Ownership guard missing');
        check(array_slice($db->parameters, -2) === [5, 'owner'], 'Ownership values missing');
    }
};
$tests['zero-row repository completion reports ownership loss'] = function() {
    $db = new TransitionPdo(); $db->affected = 0;
    $repo = new App\Repositories\NotificationRepositories\NotificationRepository($db);
    check(!$repo->markSent(5, 'stale'), 'Stale completion reported success');
};
$tests['stale recovery quarantines sending jobs'] = function() {
    $db = new TransitionPdo();
    $repo = new App\Repositories\NotificationRepositories\NotificationRepository($db);
    $repo->recoverStaleJobs();
    check(str_contains($db->sql, "delivery_state = 'sending' THEN 'uncertain'"), 'Recovery blindly retries SMTP');
    check(str_contains($db->sql, 'claim_token = NULL'), 'Recovery retains stale ownership');
};

foreach ($tests as $name => $test) {
    $test();
    echo "PASS: {$name}\n";
}
echo count($tests) . " tests passed; no SMTP connections or database connections used.\n";
