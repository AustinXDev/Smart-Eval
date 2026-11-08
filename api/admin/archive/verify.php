<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/config/archive-database.php';

$container = new \App\Core\Container($pdo, $archivePdo);
$container->archiveManagementController()->handle('verify');
