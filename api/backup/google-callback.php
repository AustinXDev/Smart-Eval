<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/init.php';

// Load the same application bootstrap used by connect.
// Example:
// require_once __DIR__ . '/../../app/bootstrap.php';

use App\Controllers\Backup\GoogleOAuthController;
use App\Services\BackupServices\GoogleOAuthService;

$controller = new GoogleOAuthController(
    new GoogleOAuthService()
);

$controller->callback();
