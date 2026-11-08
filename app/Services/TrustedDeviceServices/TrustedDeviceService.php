<?php

declare(strict_types=1);

namespace App\Services\TrustedDeviceServices;

use App\Repositories\TrustedDeviceRepo\TrustedDeviceRepository;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

final class TrustedDeviceService
{
    private const STUDENT_TRUST_DAYS = 365;
    private const ADMIN_TRUST_DAYS = 7;

    private const STUDENT_MAX_DEVICES = 3;
    private const ADMIN_MAX_DEVICES = 2;

    public function __construct(
        private TrustedDeviceRepository $repository,
        private string $appKey
    ) {

        if (strlen($appKey) < 32) {
            throw new RuntimeException(
                'A secure application key is required.'
            );
        }

    }

    private function validateAccountType(
        string $accountType
    ): void {

        if (!in_array(
            $accountType,
            ['student', 'admin'],
            true
        )) {

            throw new InvalidArgumentException(
                'Invalid account type.'
            );

        }

    }

    private function cookieName(
        string $accountType,
        string $accountId
    ): string {
        return 'smarteval_trust_' . substr(
            hash_hmac(
                'sha256',
                $accountType . ':' . $accountId,
                $this->appKey
            ),
            0,
            32
        );
    }


    private function getPolicy(
        string $accountType
    ): array {
        return match ($accountType) {
            'student' => [
                'days' => self::STUDENT_TRUST_DAYS,
                'max_devices' => self::STUDENT_MAX_DEVICES
            ],
            'admin' => [
                'days' => self::ADMIN_TRUST_DAYS,
                'max_devices' => self::ADMIN_MAX_DEVICES
            ],
            default => throw new InvalidArgumentException(
                'Invalid account type.'
            )
        };
    }


    private function cookieOptions(
        int $expires
    ): array {
        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }


    private function readToken(
        string $accountType,
        string $accountId
    ): ?array {
        $name = $this->cookieName(
            $accountType,
            $accountId
        );

        $cookie = $_COOKIE[$name] ?? null;

        if (
            !is_string($cookie) ||
            !preg_match(
                '/^[a-f0-9]{32}:[a-f0-9]{64}$/D',
                $cookie
            )
        ) {
            return null;
        }

        [$selector, $validator] = explode(
            ':',
            $cookie,
            2
        );

        return [
            'selector' => $selector,
            'validator' => $validator
        ];
    }


    public function isTrusted(
        string $accountType,
        string $accountId
    ): bool {
        $this->validateAccountType($accountType);

        $token = $this->readToken(
            $accountType,
            $accountId
        );

        if ($token === null) {
            return false;
        }

        $device = $this->repository->findBySelector(
            $token['selector'],
            $accountType,
            $accountId
        );

        if ($device === null) {
            return false;
        }

        $validator = hex2bin($token['validator']);

        if (
            $validator === false ||
            !hash_equals(
                $device['token_hash'],
                hash('sha256', $validator)
            )
        ) {
            return false;
        }

        $this->repository->updateLastUsed(
            (int) $device['device_id']
        );

        return true;
    }


    public function remember(
        string $accountType,
        string $accountId,
        string $deviceName = 'Unknown Browser'
    ): void {
        $this->validateAccountType($accountType);

        $policy = $this->getPolicy($accountType);

        $selector = bin2hex(random_bytes(16));
        $validator = random_bytes(32);

        $tokenHash = hash('sha256', $validator);

        $expires = time() + (
            $policy['days'] * 86400
        );

        $expiresAt = (new DateTimeImmutable(
            '@' . $expires
        ))
            ->setTimezone(
                new DateTimeZone('Asia/Manila')
            )
            ->format('Y-m-d H:i:s');

        // Revoke an existing registration belonging
        // to this account in the current browser.
        $existing = $this->readToken(
            $accountType,
            $accountId
        );

        if ($existing !== null) {
            $this->repository->revokeBySelector(
                $existing['selector'],
                $accountType,
                $accountId
            );
        }

        // Enforce maximum active devices.
        while (
            $this->repository->countActiveDevices(
                $accountType,
                $accountId
            ) >= $policy['max_devices']
        ) {
            if (!$this->repository->revokeOldest(
                $accountType,
                $accountId
            )) {
                throw new RuntimeException(
                    'Unable to enforce trusted-device limit.'
                );
            }
        }

        $this->repository->create(
            $accountType,
            $accountId,
            $selector,
            $tokenHash,
            mb_substr($deviceName, 0, 150),
            $expiresAt
        );

        $success = setcookie(
            $this->cookieName(
                $accountType,
                $accountId
            ),
            $selector . ':' . bin2hex($validator),
            $this->cookieOptions($expires)
        );

        if (!$success) {
            $this->repository->revokeBySelector(
                $selector,
                $accountType,
                $accountId
            );

            throw new RuntimeException(
                'Unable to set trusted-device cookie.'
            );
        }
    }

    public function listDevices(
        string $accountType,
        string $accountId
    ): array {
        $this->validateAccountType($accountType);

        $devices = $this->repository->getActiveDevices(
            $accountType,
            $accountId
        );

        $current = $this->readToken(
            $accountType,
            $accountId
        );

        foreach ($devices as &$device) {
            $device['is_current'] =
                $current !== null &&
                hash_equals(
                    $device['selector'],
                    $current['selector']
                );

            unset($device['selector']);
        }

        unset($device);

        return $devices;
    }

    public function revoke(
        string $accountType,
        string $accountId,
        int $deviceId
    ): bool {
        $this->validateAccountType($accountType);

        return $this->repository->revoke(
            $deviceId,
            $accountType,
            $accountId
        );
    }

    public function revokeAll(
        string $accountType,
        string $accountId
    ): int {
        $this->validateAccountType($accountType);

        return $this->repository->revokeAll(
            $accountType,
            $accountId
        );
    }

    public function clearCurrentCookie(
        string $accountType,
        string $accountId
    ): void {
        $this->validateAccountType($accountType);

        setcookie(
            $this->cookieName(
                $accountType,
                $accountId
            ),
            '',
            $this->cookieOptions(time() - 3600)
        );
    }


}
