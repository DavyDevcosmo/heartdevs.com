<?php

declare(strict_types=1);

namespace He4rt\Activity\Tracking\DTOs;

use DateTimeImmutable;
use He4rt\Activity\Tracking\Enums\ActivityType;

final readonly class TrackActivityDTO
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $externalIdentityId,
        public ActivityType $type,
        public DateTimeImmutable $occurredAt,
        public string $externalRef,
        public ?string $sourceType = null,
        public ?string $sourceId = null,
        public ?array $metadata = null,
    ) {}
}
