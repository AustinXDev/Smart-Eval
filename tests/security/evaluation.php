<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class EvaluationPDO extends PDO
{
    private bool $transaction = false;
    public int $rollbacks = 0;
    public function __construct() {}
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; $this->rollbacks++; return true; }
    public function inTransaction(): bool { return $this->transaction; }
}
class AnswerDouble extends \App\Repositories\EvaluationRepo\EvaluationAnswerRepository
{
    public bool $open = true;
    public array $answers = [];
    public string $comment = '';
    public int $writes = 0;
    public function __construct() {}
    public function lockOwnedEvaluation(int $evalId, string $studentId, int $teacherId): ?array {
        return $evalId === 7 && $studentId === 'owner' && $teacherId === 3
            ? ['period_id' => 1, 'set_id' => 2, 'is_open' => $this->open] : null;
    }
    public function getRequiredQuestionIds(int $setId): array { return [1, 2]; }
    public function getAnswersByEvalId(int $evalId): array { return $this->answers; }
    public function getCommentByEvalId(int $evalId): string { return $this->comment; }
    public function saveAnswer(int $evalId, array $answers): bool { $this->writes++; $this->answers = $answers; return true; }
    public function updateAnswers(int $evalId, array $answers): bool { $this->writes++; $this->answers = $answers; return true; }
    public function saveComment(int $evalId, string $comment): bool { $this->writes++; $this->comment = $comment; return true; }
}
class StatusDouble extends \App\Services\EvaluationServices\EvaluationStatusService
{
    public int $submitted = 0;
    public function __construct() {}
    public function markAsSubmitted(int $evalId): void { $this->submitted++; }
}
$pdo = new EvaluationPDO(); $repo = new AnswerDouble(); $status = new StatusDouble();
$service = new \App\Services\EvaluationServices\EvaluationAnswerService($repo, $status, $pdo);
$valid = ['eval_id' => 7, 'teacher_id' => 3, 'answers' => [1 => 5, 2 => 4], 'comment' => 'Good'];
$cases = [
    ['other owner', $valid, 'other', 403],
    ['wrong teacher', array_replace($valid, ['teacher_id' => 4]), 'owner', 403],
    ['missing question', array_replace($valid, ['answers' => [1 => 5]]), 'owner', 400],
    ['extra question', array_replace($valid, ['answers' => [1 => 5, 2 => 4, 3 => 5]]), 'owner', 400],
    ['out of range', array_replace($valid, ['answers' => [1 => 6, 2 => 4]]), 'owner', 400],
    ['string score', array_replace($valid, ['answers' => [1 => '5', 2 => 4]]), 'owner', 400],
    ['invalid answers', array_replace($valid, ['answers' => 'bad']), 'owner', 400],
    ['oversized comment', array_replace($valid, ['comment' => str_repeat('x', 5001)]), 'owner', 400],
];
foreach ($cases as [$name, $data, $owner, $expected]) {
    try { $service->submitAnswers($data, $owner); throw new RuntimeException($name . ' was accepted'); }
    catch (\App\Services\EvaluationServices\EvaluationRequestException $e) {
        if ($e->status !== $expected || $repo->writes !== 0 || $status->submitted !== 0 || $pdo->inTransaction()) {
            throw new RuntimeException($name . ' failed');
        }
    }
    echo 'PASS evaluation ' . $name . PHP_EOL;
}
$repo->open = false;
try { $service->submitAnswers($valid, 'owner'); throw new RuntimeException('Closed period accepted'); }
catch (\App\Services\EvaluationServices\EvaluationRequestException $e) {
    if ($e->status !== 409 || $repo->writes !== 0 || $pdo->inTransaction()) throw new RuntimeException('Closed period failed');
}
$repo->open = true;
$result = $service->submitAnswers($valid, 'owner');
if ($result['action'] !== 'created') throw new RuntimeException('Valid submission failed');
$writes = $repo->writes;
$result = $service->submitAnswers($valid, 'owner');
if ($result['action'] !== 'unchanged' || $repo->writes !== $writes) throw new RuntimeException('Retry duplicated writes');
$result = $service->submitAnswers(array_replace($valid, ['answers' => [1 => 4, 2 => 4]]), 'owner');
if ($result['action'] !== 'updated') throw new RuntimeException('Authorized edit failed');
echo 'PASS evaluation closed period, creation, unchanged retry, authorized edit' . PHP_EOL;
