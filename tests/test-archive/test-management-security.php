<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

class ArchiveSecurityStatement extends PDOStatement {
    public function __construct(private array $row) {}
    public function execute(?array $params = null): bool { return true; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed { return $this->row ?: false; }
}
class ArchiveSecurityPDO extends PDO {
    public function __construct(public array $row) {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new ArchiveSecurityStatement($this->row); }
}
$case = $argv[1] ?? null;
if ($case === null) {
    $cases = ['anonymous'=>401, 'dean'=>403, 'student'=>401, 'expired'=>401, 'super_admin'=>200,
        'wrong_method'=>405, 'missing_csrf'=>403, 'bad_type'=>415, 'oversized'=>413, 'preview_allowed'=>200, 'unknown_restore'=>404];
    foreach ($cases as $name => $expected) {
        $process = proc_open([PHP_BINARY, __FILE__, $name], [1=>['pipe','w'],2=>['pipe','w']], $pipes);
        $out=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); $exit=proc_close($process);
        $result=json_decode($out,true);
        if ($exit !== 0 || $error !== '' || ($result['http'] ?? null) !== $expected) throw new RuntimeException("$name failed: $out $error");
    }
    echo 'Passed ' . count($cases) . " archive endpoint authorization/CSRF/method/size checks.\n";
    exit;
}
ob_start();
register_shutdown_function(static function () {
    $body=ob_get_clean();
    echo json_encode(['http'=>http_response_code() ?: 200,'body'=>json_decode($body,true)]);
});
$_SESSION=['csrf_token'=>str_repeat('a',64),'authenticated_at'=>time(),'last_activity'=>time()];
$pdo=new ArchiveSecurityPDO(['admin_id'=>1,'username'=>'test','email'=>'test@example.invalid','role'=>$case==='dean'?'Dean':'Super Admin','password_hash'=>'test-only']);
if (!in_array($case,['anonymous','student'],true)) {
    $_SESSION['admin_authenticated']=true;
    $_SESSION['admin_id']=1;
    $_SESSION['admin_auth_version']=hash('sha256','test-only');
}
if ($case==='student') $_SESSION['student']=['student_id'=>'test'];
if ($case==='expired') $_SESSION['last_activity']=time()-1801;
$endpoint='admin/archive/list.php';
$_SERVER['REQUEST_METHOD']='GET';
if ($case==='wrong_method') $_SERVER['REQUEST_METHOD']='DELETE';
if ($case==='unknown_restore') $endpoint='admin/archive/restore.php';
if (in_array($case,['missing_csrf','bad_type','oversized','preview_allowed'],true)) {
    $endpoint='admin/archive/preview.php';
    $_SERVER['REQUEST_METHOD']='POST';
    $_SERVER['CONTENT_TYPE']=$case==='bad_type'?'text/plain':'multipart/form-data; boundary=test';
    $_SERVER['CONTENT_LENGTH']=$case==='oversized'?4*1024*1024:100;
    if ($case!=='missing_csrf') $_SERVER['HTTP_X_CSRF_TOKEN']=str_repeat('a',64);
}
\App\middleware\ApiSecurity::handle($pdo,$endpoint);
echo json_encode(['status'=>'success']);
