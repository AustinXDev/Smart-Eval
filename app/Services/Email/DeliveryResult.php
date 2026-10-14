<?php

namespace App\Services\Email;

/** Internal metadata only; never contains message content or credentials. */
final class DeliveryResult
{
    private function __construct(
        public readonly string $status,
        public readonly ?int $smtpCode = null
    ) {
    }

    public static function accepted(): self
    {
        return new self('accepted');
    }

    public static function invalidRecipient(): self
    {
        return new self('invalid_recipient');
    }

    public static function failed(?int $smtpCode): self
    {
        // No explicit SMTP rejection means acceptance may be uncertain.
        return new self($smtpCode !== null && $smtpCode >= 400 && $smtpCode <= 599
            ? 'rejected' : 'uncertain', $smtpCode);
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
