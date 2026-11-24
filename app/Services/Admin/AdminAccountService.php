<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Admin;
use App\providers\EmailProvider;
use App\Repositories\AdminRepo\AdminRepository;
use PDOException;
use RuntimeException;

/**
 * Account Management rules (super admin only; the API layer checks the role
 * and AccountPinService checks the PIN before these run).
 *
 * - Username and email must be unique (checked here with a clear message; the
 *   unique indexes from the 2026_11_24 migration are the backstop).
 * - A super admin can't delete or deactivate their own account, or change their
 *   own role.
 * - There must always be at least one active super admin.
 */
final class AdminAccountService
{
    private const ROLES = [Admin::ROLE_SUPER_ADMIN, Admin::ROLE_DEPARTMENT_HEAD];
    private const STATUSES = [Admin::STATUS_ACTIVE, Admin::STATUS_INACTIVE];

    public function __construct(
        private AdminRepository $admins,
        private ?EmailProvider $mailer = null
    ) {
    }

    /** @return array{accounts: array<int, array>, counts: array{total:int, active:int, inactive:int, super_admins:int}} */
    public function list(int $currentAdminId): array
    {
        $accounts = array_map(static function (Admin $admin) use ($currentAdminId): array {
            return $admin->toPublicArray() + ['is_self' => $admin->adminId === $currentAdminId];
        }, $this->admins->findAll());

        $active = count(array_filter($accounts, static fn ($a) => $a['status'] === Admin::STATUS_ACTIVE));

        return [
            'accounts' => $accounts,
            'counts' => [
                'total'        => count($accounts),
                'active'       => $active,
                'inactive'     => count($accounts) - $active,
                'super_admins' => count(array_filter($accounts, static fn ($a) => $a['role'] === Admin::ROLE_SUPER_ADMIN)),
            ],
        ];
    }

    /**
     * Creates the account and emails the sign-in details to the new admin.
     *
     * @return array{admin_id:int, username:string, full_name:string, email_sent:bool}
     */
    public function create(array $input, string $loginUrl): array
    {
        $data = $this->validate($input, 0, true);
        $password = (string) ($input['password'] ?? '');

        try {
            $adminId = $this->admins->create($data + [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }

        $emailSent = false;
        if ($this->mailer) {
            try {
                $emailSent = $this->mailer->send(
                    $data['email'],
                    'Your Smart-Eval administrator account',
                    AdminAccountEmail::build($data['full_name'], $data['username'], $password, Admin::roleLabel($data['role']), $loginUrl)
                );
            } catch (\Throwable $e) {
                error_log('[Account Management] welcome email failed for admin #' . $adminId . ': ' . $e->getMessage());
            }
        }

        return [
            'admin_id'   => $adminId,
            'username'   => $data['username'],
            'full_name'  => $data['full_name'],
            'email_sent' => $emailSent,
        ];
    }

    /** @return array{admin_id:int, username:string, full_name:string, status:string} */
    public function update(int $adminId, array $input, int $currentAdminId): array
    {
        $current = $this->findOrFail($adminId);
        $data = $this->validate($input, $adminId, false);

        if ($adminId === $currentAdminId) {
            if ($data['status'] !== Admin::STATUS_ACTIVE) {
                throw new AdminAccountValidationException(['status' => "You can't deactivate your own account."]);
            }
            if ($data['role'] !== $current->role) {
                throw new AdminAccountValidationException(['role' => "You can't change your own role."]);
            }
        }

        // Taking away an active super admin (demote or deactivate) needs another one left.
        $wasActiveSuper = $current->role === Admin::ROLE_SUPER_ADMIN && $current->isActive();
        $staysActiveSuper = $data['role'] === Admin::ROLE_SUPER_ADMIN && $data['status'] === Admin::STATUS_ACTIVE;
        if ($wasActiveSuper && !$staysActiveSuper && $this->admins->countActiveSuperAdmins($adminId) === 0) {
            throw new AdminAccountValidationException([
                $data['status'] !== Admin::STATUS_ACTIVE ? 'status' : 'role' => 'This is the only active super admin. Add or activate another super admin first.',
            ]);
        }

        try {
            $this->admins->updateAccount($adminId, $data);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }

        if ($current->isActive() && $data['status'] === Admin::STATUS_INACTIVE) {
            $this->admins->revokeTrustedDevices($adminId);
        }

        return [
            'admin_id'  => $adminId,
            'username'  => $data['username'],
            'full_name' => $data['full_name'],
            'status'    => $data['status'],
        ];
    }

    /** @return array{admin_id:int, username:string, full_name:string} */
    public function delete(int $adminId, int $currentAdminId): array
    {
        $current = $this->findOrFail($adminId);

        if ($adminId === $currentAdminId) {
            throw new RuntimeException("You can't delete your own account.");
        }

        if ($current->role === Admin::ROLE_SUPER_ADMIN && $current->isActive()
            && $this->admins->countActiveSuperAdmins($adminId) === 0) {
            throw new RuntimeException('This is the only active super admin and cannot be deleted.');
        }

        if (!$this->admins->deleteAccount($adminId)) {
            throw new RuntimeException('Account not found. It may have been deleted already.');
        }

        return ['admin_id' => $adminId, 'username' => $current->username, 'full_name' => $current->fullName];
    }

    /* ------------------------------------------------------------------ */

    private function findOrFail(int $adminId): Admin
    {
        $admin = $adminId > 0 ? $this->admins->findById($adminId) : null;
        if (!$admin) {
            throw new RuntimeException('Account not found. It may have been deleted.');
        }
        return $admin;
    }

    /**
     * @return array{username:string, full_name:string, email:string, role:string, status:string}
     */
    private function validate(array $input, int $exceptId, bool $isCreate): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', (string) ($input['full_name'] ?? '')) ?? '');
        $username = trim((string) ($input['username'] ?? ''));
        $email    = strtolower(trim((string) ($input['email'] ?? '')));
        $role     = trim((string) ($input['role'] ?? ''));
        $status   = trim((string) ($input['status'] ?? Admin::STATUS_ACTIVE));

        $errors = [];

        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
            $errors['full_name'] = 'Enter a full name (2–100 characters).';
        }

        if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
            $errors['username'] = 'Use 4–50 letters, numbers, dots, dashes or underscores.';
        } elseif ($this->admins->usernameExists($username, $exceptId)) {
            $errors['username'] = 'This username is already taken.';
        }

        if ($email === '' || mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($this->admins->emailExists($email, $exceptId)) {
            $errors['email'] = 'This email is already used by another account.';
        }

        if (!in_array($role, self::ROLES, true)) {
            $errors['role'] = 'Choose a role.';
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Choose Active or Inactive.';
        }

        if ($isCreate) {
            $password = (string) ($input['password'] ?? '');
            $confirm  = (string) ($input['confirm_password'] ?? '');

            if (strlen($password) < 8 || strlen($password) > 72
                || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                $errors['password'] = 'Use 8–72 characters with at least one letter and one number.';
            } elseif (!hash_equals($password, $confirm)) {
                $errors['confirm_password'] = 'The passwords do not match.';
            }
        }

        if ($errors) {
            throw new AdminAccountValidationException($errors);
        }

        return [
            'username'  => $username,
            'full_name' => $fullName,
            'email'     => $email,
            'role'      => $role,
            'status'    => $status,
        ];
    }

    /** Two saves at the same moment: the unique index catches what the check missed. */
    private function rethrowDuplicate(PDOException $e): never
    {
        if (($e->errorInfo[1] ?? null) === 1062) {
            $field = str_contains($e->getMessage(), 'uniq_admins_email') ? 'email' : 'username';
            throw new AdminAccountValidationException([
                $field => $field === 'email' ? 'This email is already used by another account.' : 'This username is already taken.',
            ]);
        }
        throw $e;
    }
}
