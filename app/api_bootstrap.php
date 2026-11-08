<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$entry = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '');

$apiRoot = str_replace('\\', '/', ROOT_PATH) . '/api/';

if (!str_starts_with(strtolower($entry), strtolower($apiRoot))) {

    \App\middleware\ApiSecurity::fail(404, 'Not found.');

}

try {

    \App\middleware\ApiSecurity::handle($pdo, substr($entry, strlen($apiRoot)));

} catch (\Throwable $e) {

    error_log('[API boundary] ' . get_class($e) . ': ' . $e->getMessage());
    \App\middleware\ApiSecurity::fail(500, 'Unable to process the request.');

}
