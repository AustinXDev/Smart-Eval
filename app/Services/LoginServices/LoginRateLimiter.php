<?php

namespace App\Services\LoginServices;

use App\Repositories\StudentRepo\Loginattemptrepository;

class LoginRateLimiter
{
  public function __construct(
    private LoginAttemptRepository $attempts, 
    private int $maxAttempts = 3,
    private int $lockMinutes = 5
  ) {

  }

  public function isLocked(string $ip, string $studentId): bool
  {
    return $this->attempts->countRecentFailures($studentId, $ip, $this->lockMinutes) >= $this->maxAttempts;
  }

  public function remainingAttempts(string $ip, string $studentId): int
  {
    $used = $this->attempts->countRecentFailures($studentId, $ip, $this->lockMinutes);
    return max($this->maxAttempts - ($used + 1), 0);
  }

  public function lockMinutes(): int
  {
      return $this->lockMinutes;
  }

  public function recordFailure(?string $studentId, string $ip): void
  {
      $this->attempts->recordFailure($studentId, $ip);
  }

  public function recordSuccess(string $studentId, string $ip): void
  {
      $this->attempts->recordSuccess($studentId, $ip);
  }

  public function clear(string $ip): void
  {
      $this->attempts->clearForIp($ip);
  }
}

?>