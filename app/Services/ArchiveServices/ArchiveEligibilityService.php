<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use DateTimeImmutable;
use DateTimeZone;

final class ArchiveEligibilityService
{
    public function isEligible(array $period): bool
    {
        if ((int) ($period['is_closed'] ?? 0) !== 1) {
            return false;
        }

        if (empty($period['end_date'])) {
            return false;
        }

        $timezone = new DateTimeZone('Asia/Manila');

        $now = new DateTimeImmutable('now', $timezone);

        $endDate = new DateTimeImmutable(
            $period['end_date'],
            $timezone
        );

        return $now >= $endDate;
    }
}
