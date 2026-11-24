<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\AdminRepo\AdminRepository;
use RuntimeException;

/**
 * 6-digit PIN that each super admin sets to open Account Management.
 *
 * - The PIN is stored with password_hash(); it is never logged or returned.
 * - Setting or resetting it requires the account password.
 * - 5 wrong PINs (or wrong passwords on the reset form) lock it for 15 minutes.
 * - A correct PIN unlocks the page for this session for 15 minutes; using the
 *   page extends that window. The account APIs check it too, so the PIN
 *   can't be skipped by calling them directly.
 */
final class AccountPinService
{
    public const MAX_ATTEMPTS = 5;
    public const LOCK_MINUTES = 15;
    public const UNLOCK_SECONDS = 900;

    private const SESSION_KEY = 'account_pin_unlock';

    public function __construct(
        private AdminRepository $admins
    ) {
    }

    /** @return array{has_pin:bool, unlocked:bool, locked_seconds:int} */
    public function state(Admin $admin): array
    {
        return [
            'has_pin'        => !empty($admin->pinHash),
            'unlocked'       => $this->isUnlocked($admin->adminId),
            'locked_seconds' => $this->admins->pinLockSecondsLeft($admin->adminId),
        ];
    }

    public function isUnlocked(int $adminId): bool
    {
        $unlock = $_SESSION[self::SESSION_KEY] ?? null;

        return is_array($unlock)
            && (int) ($unlock['admin_id'] ?? 0) === $adminId
            && (int) ($unlock['until'] ?? 0) > time();
    }

    /** For the account APIs: refuse unless unlocked, and extend the window. */
    public function requireUnlocked(int $adminId): void
    {
        if (!$this->isUnlocked($adminId)) {
            throw new AccountPinRequiredException('Enter your Account Management PIN to continue.');
        }
        $this->unlock($adminId);
    }

    public function verify(Admin $admin, string $pin): void
    {
        if (empty($admin->pinHash)) {
            throw new RuntimeException('Set up your Account Management PIN first.');
        }

        $this->assertNotLocked($admin->adminId);

        if (!preg_match('/^\d{6}$/', $pin) || !password_verify($pin, $admin->pinHash)) {
            $this->fail($admin->adminId, 'Incorrect PIN.');
        }

        $this->admins->clearPinFailures($admin->adminId);
        $this->unlock($admin->adminId);
    }

    /** First-time setup and "Forgot PIN": confirmed with the account password. */
    public function setPin(Admin $admin, string $password, string $pin, string $confirm): void
    {
        $this->assertNotLocked($admin->adminId);

        if ($password === '' || !password_verify($password, $admin->passwordHash)) {
            $this->fail($admin->adminId, 'Incorrect account password.');
        }

        if (!preg_match('/^\d{6}$/', $pin)) {
            throw new RuntimeException('The PIN must be exactly 6 digits.');
        }

        if (!hash_equals($pin, $confirm)) {
            throw new RuntimeException('The PINs do not match.');
        }

        if (self::isWeakPin($pin)) {
            throw new RuntimeException('Choose a less predictable PIN (not repeated or sequential digits).');
        }

        $this->admins->setPin($admin->adminId, password_hash($pin, PASSWORD_DEFAULT));
        $this->unlock($admin->adminId);
    }

    public function lock(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /** 000000, 111111 … and 012345 / 123456 / 987654 style runs. */
    public static function isWeakPin(string $pin): bool
    {
        if (preg_match('/^(\d)\1{5}$/', $pin)) {
            return true;
        }

        return str_contains('0123456789', $pin) || str_contains('9876543210', $pin);
    }

    private function unlock(int $adminId): void
    {
        $_SESSION[self::SESSION_KEY] = ['admin_id' => $adminId, 'until' => time() + self::UNLOCK_SECONDS];
    }

    private function assertNotLocked(int $adminId): void
    {
        $left = $this->admins->pinLockSecondsLeft($adminId);

        if ($left > 0) {
            throw new RuntimeException(sprintf(
                'Too many attempts. Try again in %d minute%s.',
                (int) ceil($left / 60),
                ceil($left / 60) == 1 ? '' : 's'
            ));
        }
    }

    private function fail(int $adminId, string $message): never
    {
        $this->admins->recordPinFailure($adminId, self::MAX_ATTEMPTS, self::LOCK_MINUTES);

        $left = $this->admins->pinLockSecondsLeft($adminId);
        if ($left > 0) {
            throw new RuntimeException(sprintf(
                '%s Too many attempts, so Account Management is locked for %d minutes.',
                $message,
                self::LOCK_MINUTES
            ));
        }

        $admin = $this->admins->findById($adminId);
        $remaining = max(0, self::MAX_ATTEMPTS - ($admin?->pinFailedAttempts ?? 0));

        throw new RuntimeException(sprintf('%s %d attempt%s left.', $message, $remaining, $remaining === 1 ? '' : 's'));
    }
}
