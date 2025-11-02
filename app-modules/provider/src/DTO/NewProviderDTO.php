<?php

declare(strict_types=1);

namespace He4rt\Provider\DTO;

use He4rt\Provider\Enums\ProviderEnum;
use JsonSerializable;

final readonly class NewProviderDTO implements JsonSerializable
{
    public function __construct(
        private ProviderEnum $provider,
        private string $providerId
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'provider' => $this->provider->value,
            'provider_id' => $this->providerId,
        ];
    }
}
