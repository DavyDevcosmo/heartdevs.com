<?php

declare(strict_types=1);

namespace He4rt\Message\DTOs;

use Heart\Character\Domain\Enums\VoiceStatesEnum;
use Heart\Provider\Enums\ProviderEnum;

final readonly class NewVoiceMessageDTO
{
    public function __construct(
        public ProviderEnum $provider,
        public string $providerId,
        public VoiceStatesEnum $voiceState,
        public string $channelName,
    ) {}

    public static function make(array $payload): self
    {
        return new self(
            provider: ProviderEnum::from($payload['provider']),
            providerId: $payload['provider_id'],
            voiceState: VoiceStatesEnum::from($payload['state']),
            channelName: $payload['channel_name']
        );
    }
}
