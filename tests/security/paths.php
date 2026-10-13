<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use App\middleware\ApiSecurity;
$cases = [
    ['/account/Smart-Eval/api/admin/auth/login.php', '/account/Smart-Eval/api', 'admin/auth/login.php'],
    ['/account/public_html/api/admin/auth/login.php', '/account/public_html/api', 'admin/auth/login.php'],
    ['/account/public_html/api/csrf.php', '/account/public_html/api/', 'csrf.php'],
    ['/account/public_html/api-other/login.php', '/account/public_html/api', null],
    ['/account/private/login.php', '/account/public_html/api', null],
    ['/account/public_html/api/../private.php', '/account/public_html/api', null],
    ['', '/account/public_html/api', null],
];
foreach ($cases as [$entry, $root, $expected]) {
    if (ApiSecurity::endpointFromPaths($entry, $root) !== $expected) throw new RuntimeException('API path test failed');
}
$root = realpath(dirname(__DIR__, 2) . '/api');
$entry = realpath($root . '/admin/auth/login.php');
if (ApiSecurity::endpointFromPaths($entry, $root) !== 'admin/auth/login.php') throw new RuntimeException('Actual filesystem path failed');
echo 'PASS local/split deployment paths and outside-root rejection' . PHP_EOL;
