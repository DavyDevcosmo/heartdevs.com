<?php

declare(strict_types=1);

namespace He4rt\Meeting\DTO;

use Heart\Provider\Domain\Enums\ProviderEnum;

final readonly class NewMeetingDTO
{
    public function __construct(
        public ProviderEnum $provider,
        public string $providerId,
        public int $meetingTypeId,
    ) {}

    public static function make(string $provider, string $providerId, int $meetingTypeId): self
    {
        return new self(
            provider: ProviderEnum::from($provider),
            providerId: $providerId,
            meetingTypeId: $meetingTypeId
        );
    }
}
