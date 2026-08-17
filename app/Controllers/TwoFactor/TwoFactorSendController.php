<?php

namespace App\Controllers\TwoFactor;

use App\Services\TwoFactorServices\TwoFactorService;

class TwoFactorSendController
{
    public function __construct(
        private TwoFactorService $twoFactor
    ) {
    }

    public function send(
        string $studentId,
        string $purpose
    ): array {

        return $this->twoFactor->sendCode(
            $studentId,
            $purpose
        );
    }

    public function resend(
      string $studentId,
      string $purpose
    ): array {

      return $this->twoFactor->resendCode(
        $studentId,
        $purpose
      ); 

    }
}