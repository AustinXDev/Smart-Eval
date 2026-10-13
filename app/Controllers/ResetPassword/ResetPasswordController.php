<?php
namespace App\Controllers\ResetPassword;

use App\Services\ResetPasswordServices\ResetPasswordServices;

class ResetPasswordController
{
    public function __construct(private ResetPasswordServices $service) {}

    public function handle(array $input): array
    {
        try {
            return $this->service->reset(trim($input['token'] ?? ''), $input['password'] ?? '');
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => \App\middleware\ApiSecurity::publicMessage($e)];
        }
    }
}
