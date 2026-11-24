<?php

declare(strict_types=1);

// Account Management · POST JSON {admin_id}: delete an account.
// Access (super admin only), method and CSRF are enforced by ApiSecurity (app/api_bootstrap.php).

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

use App\Controllers\Admin\AdminAccountController;

AdminAccountController::respond(
    static fn () => AdminAccountController::make($pdo)->delete(AdminAccountController::input()),
    'Account deleted.'
);
