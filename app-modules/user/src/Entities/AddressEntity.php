<?php

declare(strict_types=1);

namespace He4rt\User\Entities;

use JsonSerializable;

final class AddressEntity implements JsonSerializable
{
    public function __construct(
        public string $id,
        private readonly ?string $country,
        private readonly ?string $state,
    ) {}

    public static function make(array $payload): self
    {
        return new self(
            id: $payload['id'],
            country: $payload['country'],
            state: $payload['state'],
            //            city: $payload['city'],
            //            zipCode: $payload['zip_code']
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'country' => $this->country,
            'state' => $this->state,
        ];
    }
}
