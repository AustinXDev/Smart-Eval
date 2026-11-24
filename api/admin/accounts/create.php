<?php

declare(strict_types=1);

// Account Management · POST JSON: create an account and email the sign-in details to the new admin.
// Access (super admin only), method and CSRF are enforced by ApiSecurity (app/api_bootstrap.php).

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

use App\Controllers\Admin\AdminAccountController;

AdminAccountController::respond(
    static fn () => AdminAccountController::make($pdo, true)->create(AdminAccountController::input(), BASE_URL . 'admin-login'),
    'Account created.'
);
