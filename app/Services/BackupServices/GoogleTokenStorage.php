<?php

declare(strict_types=1);

namespace App\Services\BackupServices;

use RuntimeException;

final class GoogleTokenStorage
{
    private string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__, 3)
            . '/storage/private/google-refresh-token.json';
    }

    public function save(string $refreshToken): void
    {
        if ($refreshToken === '') {
            throw new RuntimeException(
                'Refresh token cannot be empty.'
            );
        }

        $directory = dirname($this->path);

        if (!is_dir($directory) &&
            !mkdir($directory, 0700, true) &&
            !is_dir($directory)) {
            throw new RuntimeException(
                'Unable to create token directory.'
            );
        }

        $data = json_encode(
            ['refresh_token' => $refreshToken],
            JSON_THROW_ON_ERROR
        );

        if (file_put_contents(
            $this->path,
            $data,
            LOCK_EX
        ) === false) {
            throw new RuntimeException(
                'Unable to save Google refresh token.'
            );
        }

        @chmod($this->path, 0600);
    }

    public function get(): string
    {
        if (!is_file($this->path)) {
            throw new RuntimeException(
                'Google Drive is not authorized.'
            );
        }

        $data = json_decode(
            file_get_contents($this->path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $token = $data['refresh_token'] ?? null;

        if (!is_string($token) || $token === '') {
            throw new RuntimeException(
                'Invalid Google refresh token.'
            );
        }

        return $token;
    }
}
