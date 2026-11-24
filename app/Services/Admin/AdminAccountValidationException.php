<?php

declare(strict_types=1);

namespace App\Services\Admin;

/** Invalid account form: carries one message per field for the form to show. */
final class AdminAccountValidationException extends \RuntimeException
{
    /** @param array<string, string> $errors field => message */
    public function __construct(private array $errors)
    {
        parent::__construct(reset($errors) ?: 'Please check the highlighted fields.');
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
