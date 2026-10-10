<?php

declare(strict_types=1);

namespace App\middleware;

class AdminAuthMiddleware
{
    /**
     * Authenticate an administrator for browser pages.
     */
    public static function handle(): void
    {

        if (
            empty($_SESSION['admin_authenticated']) ||
            empty($_SESSION['admin_id'])
        ) {

            header('Location: admin-login');
            exit;

        }

    }


    /**
     * Authenticate an administrator for API requests.
     * Returns JSON instead of redirecting.
     */
    public static function handleApi(): void
    {
        if (!self::isAuthenticated()) {
            self::jsonError(
                401,
                401,
                'Unauthenticated. Please log in again.'
            );
        }
    }


    /**
    * Check the administrator's session.
    */
    private static function isAuthenticated(): bool
    {
        return ($_SESSION['admin_authenticated'] ?? false) === true
            && isset($_SESSION['admin_id'])
            && is_scalar($_SESSION['admin_id'])
            && (string) $_SESSION['admin_id'] !== '';
    }


    private static function jsonError(
        int $status,
        int $code,
        string $message
    ): never {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'success' => false,
            'code'    => $code,
            'message' => $message,
        ]);

        exit;
    }



}
