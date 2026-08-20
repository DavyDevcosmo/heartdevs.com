<?php

declare(strict_types=1);

namespace He4rt\Profile\DTOs;

/**
 * One job on the public profile.
 *
 * Dates arrive already formatted as a period string: the page shows month and
 * year, never the raw date, and a past job with no recorded end date shows only
 * its start instead of inventing "until today".
 */
final readonly class WorkExperienceData
{
    public function __construct(
        public string $company,
        public string $position,
        public string $period,
        public ?string $description = null,
        public ?string $duration = null,
        public bool $isCurrent = false,
    ) {}
}
