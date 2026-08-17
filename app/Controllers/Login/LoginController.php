<?php

namespace App\Controllers\Login;

use App\Services\LoginServices\AuthException;
use App\Services\LoginServices\AuthService;

class LoginController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function handle(
        array $input,
        string $ip
    ): array {

        $studentId = isset($input['student_id'])
            ? trim((string) $input['student_id'])
            : '';

        $password = isset($input['password'])
            ? (string) $input['password']
            : '';

        try {

            $result = $this->authService->login(
                $studentId,
                $password,
                $ip
            );

            /*
             * Password is correct, but 2FA
             * still needs to be completed.
             */
            if ($result['status'] === '2fa_required') {

                return [
                    'status' => '2fa_required',
                    'message' => $result['message']
                ];
                
            }

            return [
                'status' => 'success',
                'message' => 'Login successful!',
                'redirect' => $result['redirect'] ?? null
            ];

        } catch (AuthException $e) {

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}