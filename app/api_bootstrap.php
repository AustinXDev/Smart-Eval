<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

// Trusted deployment configuration. Never derive the API root from REQUEST_URI.
$configuredApiRoot = trim($_ENV['APP_API_ROOT'] ?? '');
$apiRoots = $configuredApiRoot !== ''
    ? [$configuredApiRoot]
    : [ROOT_PATH . '/api', dirname(ROOT_PATH) . '/public_html/api'];
$entry = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
$endpoint = null;
foreach ($apiRoots as $candidate) {
    $canonicalRoot = realpath($candidate);
    if ($entry === false || $canonicalRoot === false) continue;
    $endpoint = \App\middleware\ApiSecurity::endpointFromPaths($entry, $canonicalRoot);
    if ($endpoint !== null) break;
}
if ($endpoint === null) {
    error_log('[API boundary] Entry script is outside the configured API directory.');
    \App\middleware\ApiSecurity::fail(404, 'Not found.');
}
try {
    \App\middleware\ApiSecurity::handle($pdo, $endpoint);
} catch (\Throwable $e) {
    error_log('[API boundary] ' . get_class($e) . ': ' . $e->getMessage());
    \App\middleware\ApiSecurity::fail(500, 'Unable to process the request.');
}
