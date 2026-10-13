<?php
namespace App\Services\LoginServices;

final class PasswordPolicy
{
    public static function validate(string $password): void
    {
        if (strlen($password) < 8 || strlen($password) > 72
            || str_contains($password, "\0") || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)
            || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new \RuntimeException('Password must be 8 to 72 bytes and include uppercase, lowercase, a number, and a symbol.');
        }
    }
}
