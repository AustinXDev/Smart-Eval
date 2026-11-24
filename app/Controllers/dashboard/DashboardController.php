<?php

namespace App\Controllers\dashboard;

use App\Services\Admin\AdminContext;
use App\Services\DashboardServices\DashboardService;
use Throwable;

class DashboardController
{
    public function __construct(
        private AdminContext $adminContext,
        private array $navigation
    ) {
    }

    public function index(): array
    {
        $admin = $this->adminContext->getCurrentAdmin();

        $department = $_GET['dept'] ?? '';

        // 'super admin' -> 'super_admin', 'department head' -> 'dean' (nav.php keys).
        $role = $admin->roleKeyValue();

        return [
            'admin' => $admin,
            'department' => $department,
            'role' => $role,
            'navigation' => $this->navigation[$role] ?? []
        ];
    }

}
