<?php
// Isolated tests: fake PDO only, no bootstrap, database, mail, or network calls.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$cases = ['anonymous' => 401, 'pending' => 401, 'dean_manage' => 403,
    'dean_read' => 200, 'admin_write' => 200, 'csrf_missing' => 403,
    'bad_type' => 415, 'oversize' => 413, 'wrong_method' => 405, 'unknown' => 404,
    'student_cross_department' => 403, 'student_teacher_read' => 200,
    'expired' => 401, 'changed_password' => 401, 'csrf_read' => 200];
foreach ($cases as $case => $status) {
    $process = proc_open([PHP_BINARY, __DIR__ . '/api_case.php', $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $out = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($process);
    $result = json_decode($out, true);
    if ($exit !== 0 || ($result['http'] ?? null) !== $status || $error !== '') {
        throw new RuntimeException($case . ' failed: ' . $out . $error);
    }
    echo 'PASS ' . $case . PHP_EOL;
}
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/api')) as $file) {
    if ($file->getExtension() !== 'php') continue;
    $endpoint = str_replace('\\', '/', substr($file->getPathname(), strlen(dirname(__DIR__, 2) . '/api/')));
    if (\App\middleware\ApiSecurity::policy($endpoint) === null) throw new RuntimeException('Missing policy: ' . $endpoint);
    if (!str_contains(file_get_contents($file->getPathname()), '/app/api_bootstrap.php')) throw new RuntimeException('Missing bootstrap: ' . $endpoint);
}
echo 'PASS complete endpoint policy/bootstrap coverage' . PHP_EOL;

require __DIR__ . '/evaluation.php';

foreach (['short', 'abcdefgh!', 'ABCDEFGH1!', 'Abcdefgh!', 'Abcdefgh1', str_repeat('A', 73) . 'a1!'] as $password) {
    try {
        \App\Services\LoginServices\PasswordPolicy::validate($password);
        throw new \LogicException('Weak password accepted');
    } catch (\RuntimeException $e) {}
}
\App\Services\LoginServices\PasswordPolicy::validate('ValidPassword1!');
echo 'PASS server password policy' . PHP_EOL;

require __DIR__ . '/reset.php';

require __DIR__ . '/paths.php';
