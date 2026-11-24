<?php

declare(strict_types=1);

// Account Management · GET: all administrator accounts + counts (super admin, PIN unlocked).
// Access (super admin only), method and CSRF are enforced by ApiSecurity (app/api_bootstrap.php).

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

use App\Controllers\Admin\AdminAccountController;

AdminAccountController::respond(static fn () => AdminAccountController::make($pdo)->list());
