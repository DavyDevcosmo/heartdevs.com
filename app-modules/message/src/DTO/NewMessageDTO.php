<?php

declare(strict_types=1);

namespace He4rt\Message\DTO;

use DateTimeImmutable;
use He4rt\Provider\Enums\ProviderEnum;

final class NewMessageDTO
{
    public function __construct(
        public ProviderEnum $provider,
        public string $providerId,
        public string $providerMessageId,
        public string $channelId,
        public string $content,
        public DateTimeImmutable $sentAt,
    ) {}

    public static function make(array $payload): self
    {
        return new self(
            provider: ProviderEnum::from($payload['provider']),
            providerId: $payload['provider_id'],
            providerMessageId: $payload['provider_message_id'],
            channelId: $payload['channel_id'],
            content: $payload['content'],
            sentAt: new DateTimeImmutable($payload['sent_at'])
        );
    }
}
