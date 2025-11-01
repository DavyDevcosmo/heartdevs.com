<?php

declare(strict_types=1);

namespace He4rt\Character\Entities;

use DateInterval;
use DateTimeImmutable;

final class DailyRewardEntity
{
    public ?DateTimeImmutable $claimedAt;

    public function __construct(?string $claimedAt)
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $claimedAt);
        if (! $dateTime) {
            $dateTime = null;
        }

        $this->claimedAt = $dateTime;
    }

    public function canClaim(): bool
    {
        if (is_null($this->claimedAt)) {
            return true;
        }

        $dateTimeInterval = DateInterval::createFromDateString('1 day');
        $oneDayLater = (clone $this->claimedAt)->add($dateTimeInterval);
        $now = new DateTimeImmutable(now());

        return $now > $oneDayLater;
    }

    public function minutesUntilClaim(): int
    {
        return 1;
    }
}
