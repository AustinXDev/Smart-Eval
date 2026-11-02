<?php

declare(strict_types=1);

namespace App\Controllers\Backup;

use App\Services\BackupServices\GoogleOAuthService;
use App\Services\BackupServices\GoogleTokenStorage;
use RuntimeException;
use Throwable;

final class GoogleOAuthController
{
    public function __construct(
        private GoogleOAuthService $oauthService
    ) {
    }

    /**
     * Start Google Drive OAuth authorization.
     */
    public function connect(): void
    {
        $this->requireAuthorizedSetup();

        try {
            $state = bin2hex(random_bytes(32));

            $_SESSION['google_oauth_state_hash'] =
                hash('sha256', $state);

            $_SESSION['google_oauth_created_at'] = time();

            $authorizationUrl = $this->oauthService
                ->getAuthorizationUrl($state);

            session_write_close();

            header('Location: ' . $authorizationUrl, true, 302);
            exit;

        } catch (Throwable $e) {
            error_log(
                'Google OAuth initialization failed: ' .
                get_class($e)
            );

            http_response_code(500);
            exit('Unable to initialize Google authorization.');
        }
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(): void
    {
        $this->requireAuthorizedSetup();

        $expectedHash =
            $_SESSION['google_oauth_state_hash'] ?? null;

        $createdAt =
            $_SESSION['google_oauth_created_at'] ?? null;

        // Consume the OAuth state.
        unset(
            $_SESSION['google_oauth_state_hash'],
            $_SESSION['google_oauth_created_at']
        );

        $receivedState = $_GET['state'] ?? null;
        $now = time();

        if (
            !is_string($receivedState) ||
            !is_string($expectedHash) ||
            !is_int($createdAt) ||
            $createdAt > $now ||
            ($now - $createdAt) > 600 ||
            !hash_equals(
                $expectedHash,
                hash('sha256', $receivedState)
            )
        ) {
            http_response_code(400);
            exit('Invalid or expired OAuth state.');
        }

        if (isset($_GET['error'])) {
            http_response_code(400);
            exit('Google authorization was denied.');
        }

        $code = $_GET['code'] ?? null;

        if (!is_string($code) || $code === '') {
            http_response_code(400);
            exit('Missing authorization code.');
        }

        try {
            $token = $this->oauthService
                ->exchangeCode($code);

            $refreshToken = $token['refresh_token'] ?? null;

            if (
                !is_string($refreshToken) ||
                $refreshToken === ''
            ) {
                throw new RuntimeException(
                    'Google did not return a refresh token.'
                );
            }

            $storage = new GoogleTokenStorage();
            $storage->save($refreshToken);

            header('Content-Type: text/plain; charset=utf-8');

            echo 'Google Drive authorization successful.';

        } catch (Throwable $e) {
            error_log(
                'Google OAuth failed: ' . get_class($e)
            );

            http_response_code(500);
            exit('Google Drive authorization failed.');
        }
    }

    /**
     * Restrict interactive OAuth setup.
     *
     * This method is NOT used by automatic cron backups.
     */
    private function requireAuthorizedSetup(): void
    {
        if (
            ($_ENV['GOOGLE_OAUTH_SETUP_ENABLED'] ?? 'false')
            !== 'true'
        ) {
            http_response_code(403);
            exit('Google OAuth setup is disabled.');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Must match your actual admin authentication session.
        if (
            ($_SESSION['admin_authenticated'] ?? false) !== true ||
            !isset($_SESSION['admin_id'])
        ) {
            http_response_code(403);
            exit('Administrator access required.');
        }

        // Require HTTPS in production.
        if (
            ($_ENV['APP_ENV'] ?? '') === 'production' &&
            (
                empty($_SERVER['HTTPS']) ||
                $_SERVER['HTTPS'] === 'off'
            )
        ) {
            http_response_code(403);
            exit('HTTPS is required.');
        }
    }
}
