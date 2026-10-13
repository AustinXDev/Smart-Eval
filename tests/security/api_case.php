<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';

class SecurityStatement extends PDOStatement
{
    public function __construct(private array $row) {}
    public function execute(?array $params = null): bool { return true; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed { return $this->row ?: false; }
}
class SecurityPDO extends PDO
{
    public function __construct(public array $row = []) {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new SecurityStatement($this->row); }
}
ob_start();
register_shutdown_function(function () {
    $body = ob_get_clean();
    echo json_encode(['http' => http_response_code() ?: 200, 'body' => json_decode($body, true)]);
});
$scenario = $argv[1] ?? ($_GET['scenario'] ?? '');
$_SESSION = ['csrf_token' => str_repeat('a', 64), 'authenticated_at' => time(), 'last_activity' => time()];
$_SERVER['REQUEST_METHOD'] = PHP_SAPI === 'cli' ? 'GET' : $_SERVER['REQUEST_METHOD'];
$endpoint = 'student/get_all_students.php';
$pdo = new SecurityPDO();
if (in_array($scenario, ['dean_manage', 'dean_read', 'admin_write', 'csrf_missing', 'bad_type', 'bad_json', 'json_scalar', 'oversize'], true)) {
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_auth_version'] = hash('sha256', 'unused');
    $pdo->row = ['admin_id' => 1, 'username' => 'test', 'email' => 'test@example.com', 'role' => $scenario === 'dean_manage' || $scenario === 'dean_read' ? 'Dean' : 'Super Admin', 'password_hash' => 'unused'];
}
if ($scenario === 'dean_read') $endpoint = 'admin/analytics/analytics.php';
if ($scenario === 'pending') { $_SESSION['admin_2fa_pending'] = true; $_SESSION['admin_2fa_id'] = 1; }
if (in_array($scenario, ['admin_write', 'csrf_missing', 'bad_type', 'bad_json', 'json_scalar', 'oversize'], true)) {
    $endpoint = 'program/create.php';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    if ($scenario !== 'csrf_missing') $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('a', 64);
    if ($scenario === 'bad_type') $_SERVER['CONTENT_TYPE'] = 'text/plain';
    if ($scenario === 'oversize') $_SERVER['CONTENT_LENGTH'] = 4 * 1024 * 1024;
}
if ($scenario === 'wrong_method') $_SERVER['REQUEST_METHOD'] = 'DELETE';
if ($scenario === 'unknown') $endpoint = 'unknown.php';
if ($scenario === 'student_cross_department' || $scenario === 'student_teacher_read') {
    $_SESSION['student'] = ['student_id' => 'student-a', 'auth_version' => hash('sha256', '')];
    $pdo->row = ['student_id' => 'student-a', 'full_name' => 'Test', 'email' => 'test@example.com', 'program_id' => 1, 'department' => 'college', 'account_status' => 'active', 'is_active' => 1];
    $endpoint = 'teacher/get_teachers.php';
    $_GET['department'] = $scenario === 'student_cross_department' ? 'shs' : 'college';
}
if ($scenario === 'json_body') $endpoint = 'admin/auth/login.php';
if ($scenario === 'expired') {
    $_SESSION['admin_authenticated'] = true; $_SESSION['admin_id'] = 1;
    $_SESSION['last_activity'] = time() - 1801;
}
if ($scenario === 'changed_password') {
    $_SESSION['admin_authenticated'] = true; $_SESSION['admin_id'] = 1;
    $_SESSION['admin_auth_version'] = hash('sha256', 'old');
    $pdo->row = ['admin_id' => 1, 'username' => 'test', 'email' => 'test@example.com', 'role' => 'Super Admin', 'password_hash' => 'changed'];
}
if ($scenario === 'csrf_read') $endpoint = 'csrf.php';
\App\middleware\ApiSecurity::handle($pdo, $endpoint);
echo json_encode(['status' => 'success', 'token' => $endpoint === 'csrf.php' ? \App\middleware\ApiSecurity::csrfToken() : null]);
