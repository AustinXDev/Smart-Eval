<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Standalone, synthetic-data tests. No application bootstrap or database writes.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Services\BackupServices\{BackupPreviewService, BackupEncryptionService, DatabaseExportService};
use App\Services\ArchiveServices\ArchiveManagementService;
use App\middleware\ApiSecurity;

$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
function rejects(callable $callback, string $message): void {
    try { $callback(); } catch (InvalidArgumentException $e) { check(true, $message); return; }
    throw new RuntimeException('Accepted invalid input: ' . $message);
}

$tables = [
    'evaluation_periods' => [[
        'period_id' => 17, 'academic_year' => '2025-2026', 'semester' => '1st Semester',
        'target_dept' => 'college', 'set_id' => 3, 'is_closed' => 1,
    ]],
    'question_set_snapshots' => [['period_id' => 17, 'set_id' => 3]],
    'question_snapshots' => [['period_id' => 17, 'question_id' => 7, 'set_id' => 3]],
    'teacher_snapshots' => [['period_id' => 17, 'teacher_id' => 11]],
    'student_snapshots' => [['period_id' => 17, 'student_pk' => 13, 'student_id' => 'S-13', 'program_id' => 5]],
    'program_snapshots' => [['period_id' => 17, 'program_id' => 5]],
    'evaluation_status' => [['period_id' => 17, 'eval_id' => 19, 'student_pk' => 13, 'student_id' => 'S-13', 'teacher_id' => 11, 'is_submitted' => 1]],
    'evaluation_answers' => [['period_id' => 17, 'answer_id' => 23, 'eval_id' => 19, 'question_id' => 7, 'score' => 5]],
    'participation_history' => [],
    'notification_queue' => [],
    'teacher_notification_queue' => [['period_id' => 17, 'queue_id' => 29, 'teacher_id' => 11]],
];
$schema = [];
foreach (DatabaseExportService::TABLES as $table) {
    $row = $tables[$table][0] ?? ['period_id' => 17];
    $schema[$table] = ['columns' => [], 'indexes' => []];
    foreach ($row as $field => $value) {
        $type = is_int($value) ? 'int(11)' : 'varchar(100)';
        if ($field === 'target_dept') $type = "enum('college','shs')";
        $schema[$table]['columns'][] = ['Field' => $field, 'Type' => $type, 'Null' => $field === 'student_pk' ? 'YES' : 'NO'];
    }
    $key = match ($table) {
        'evaluation_status' => 'eval_id', 'evaluation_answers' => 'answer_id',
        'teacher_notification_queue' => 'queue_id', default => 'period_id',
    };
    $schema[$table]['indexes'][] = ['Non_unique' => 0, 'Key_name' => 'PRIMARY', 'Seq_in_index' => 1, 'Column_name' => $key];
}
$schema['evaluation_answers']['indexes'][] = ['Non_unique' => 0, 'Key_name' => 'uq_eval_question', 'Seq_in_index' => 1, 'Column_name' => 'eval_id'];
$schema['evaluation_answers']['indexes'][] = ['Non_unique' => 0, 'Key_name' => 'uq_eval_question', 'Seq_in_index' => 2, 'Column_name' => 'question_id'];
$payload = ['manifest' => [
    'format' => 'smart-eval-period-backup', 'version' => 1, 'period_id' => 17,
    'exported_at' => '2026-11-04T01:00:00+00:00',
    'record_counts' => array_map('count', $tables),
], 'tables' => $tables];
$result = BackupPreviewService::validatePayload($payload, $schema);
check($result['period_id'] === 17 && $result['submissions'] === 1 && $result['answers'] === 1, 'Valid preview metadata');
check($result['total_records'] === array_sum(array_map('count', $tables)), 'Total counts');
$mutations = [
    'unknown format' => function (&$p) { $p['manifest']['format'] = 'sql'; },
    'unsupported version' => function (&$p) { $p['manifest']['version'] = 2; },
    'invalid period' => function (&$p) { $p['manifest']['period_id'] = -1; },
    'invalid date' => function (&$p) { $p['manifest']['exported_at'] = '2026-02-31T01:00:00+00:00'; },
    'missing table' => function (&$p) { unset($p['tables']['notification_queue']); },
    'unexpected table' => function (&$p) { $p['tables']['admins'] = []; },
    'missing count' => function (&$p) { unset($p['manifest']['record_counts']['notification_queue']); },
    'mismatched count' => function (&$p) { $p['manifest']['record_counts']['evaluation_status'] = 99; },
    'non-list table' => function (&$p) { $p['tables']['evaluation_status'] = ['row' => $p['tables']['evaluation_status'][0]]; },
    'cross-period record' => function (&$p) { $p['tables']['evaluation_answers'][0]['period_id'] = 18; },
    'unexpected column' => function (&$p) { $p['tables']['evaluation_answers'][0]['sql'] = 'bad'; },
    'missing column' => function (&$p) { unset($p['tables']['evaluation_answers'][0]['eval_id']); },
    'nested value' => function (&$p) { $p['tables']['evaluation_periods'][0]['academic_year'] = []; },
    'oversized string' => function (&$p) { $p['tables']['evaluation_periods'][0]['academic_year'] = str_repeat('x', 101); },
    'invalid enum' => function (&$p) { $p['tables']['evaluation_periods'][0]['target_dept'] = 'other'; },
    'open period' => function (&$p) { $p['tables']['evaluation_periods'][0]['is_closed'] = 0; },
    'duplicate primary key' => function (&$p) { $p['tables']['evaluation_answers'][] = $p['tables']['evaluation_answers'][0]; $p['manifest']['record_counts']['evaluation_answers']++; },
    'duplicate unique key' => function (&$p) { $r = $p['tables']['evaluation_answers'][0]; $r['answer_id'] = 24; $p['tables']['evaluation_answers'][] = $r; $p['manifest']['record_counts']['evaluation_answers']++; },
    'missing teacher' => function (&$p) { $p['tables']['evaluation_status'][0]['teacher_id'] = 999; },
    'missing student' => function (&$p) { $p['tables']['evaluation_status'][0]['student_pk'] = 999; },
    'mismatched student identity' => function (&$p) { $p['tables']['evaluation_status'][0]['student_id'] = 'wrong'; },
    'missing evaluation' => function (&$p) { $p['tables']['evaluation_answers'][0]['eval_id'] = 999; },
    'missing question' => function (&$p) { $p['tables']['evaluation_answers'][0]['question_id'] = 999; },
    'missing question set' => function (&$p) { $p['tables']['question_snapshots'][0]['set_id'] = 999; },
    'missing program' => function (&$p) { $p['tables']['student_snapshots'][0]['program_id'] = 999; },
    'missing notification teacher' => function (&$p) { $p['tables']['teacher_notification_queue'][0]['teacher_id'] = 999; },
];
foreach ($mutations as $name => $mutate) {
    $bad = $payload;
    $mutate($bad);
    rejects(fn () => BackupPreviewService::validatePayload($bad, $schema), $name);
}
foreach (['overview', 'list', 'details', 'logs'] as $endpoint) {
    check(ApiSecurity::policy("admin/archive/$endpoint.php") === ['GET', 'admin_manage'], 'Read endpoint requires super-admin management access');
}
foreach (['preview', 'verify'] as $endpoint) {
    check(ApiSecurity::policy("admin/archive/$endpoint.php") === ['POST', 'admin_manage'], 'POST endpoint requires super-admin management access');
}
check(ApiSecurity::policy('admin/archive/restore.php') === null, 'No restoration endpoint');
check(ApiSecurity::policy('admin/archive/delete.php') === null, 'No deletion endpoint');
foreach ([null, [], 0, -1, '1 OR 1=1', '1.5'] as $id) rejects(fn () => ArchiveManagementService::positiveId($id), 'Invalid identifier');
check(ArchiveManagementService::positiveId('17') === 17, 'Valid identifier');

$directory = sys_get_temp_dir() . '/smarteval-management-test-' . bin2hex(random_bytes(8));
if (!mkdir($directory, 0700)) throw new RuntimeException('Cannot create test temporary directory.');
try {
    $encryption = new BackupEncryptionService(random_bytes(32));
    file_put_contents($directory . '/fixture.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $encrypted = $encryption->encryptFile($directory . '/fixture.json');
    $encryption->decryptFile($encrypted['file_path'], $directory . '/decoded.json');
    check(file_get_contents($directory . '/fixture.json') === file_get_contents($directory . '/decoded.json'), 'Existing encryption format round trip');
    $bytes = file_get_contents($encrypted['file_path']);
    $bytes[strlen($bytes) - 1] = chr(ord($bytes[strlen($bytes) - 1]) ^ 1);
    file_put_contents($directory . '/damaged.enc', $bytes);
    $failed = false;
    try { $encryption->decryptFile($directory . '/damaged.enc', $directory . '/damaged.json'); }
    catch (RuntimeException $e) { $failed = true; }
    check($failed, 'Tampered encrypted bytes rejected');
} finally {
    foreach (glob($directory . '/*') as $file) unlink($file);
    rmdir($directory);
}
echo "Passed $checks archive management checks. No database records were changed.\n";
