<?php

declare(strict_types=1);

namespace He4rt\User\Entities;

use He4rt\Badge\Collections\BadgeCollection;
use He4rt\Character\Collections\PastSeasonCollection;
use He4rt\Character\Entities\CharacterEntity;
use JsonSerializable;

final readonly class ProfileEntity implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $username,
        public InformationEntity $informationEntity,
        public AddressEntity $addressEntity,
        public array $characterEntity,
        public array $connectedProviders,
        public BadgeCollection $badges,
        public PastSeasonCollection $pastSeasons,
    ) {}

    public static function make(array $payload): self
    {
        $badges = BadgeCollection::make($payload['character']['badges']);
        $pastSeasons = PastSeasonCollection::make($payload['character']['past_seasons']);

        return new self(
            id: $payload['id'],
            username: $payload['username'],
            informationEntity: InformationEntity::make($payload['information']),
            addressEntity: AddressEntity::make($payload['address']),
            characterEntity: CharacterEntity::make($payload['character'])->jsonSerialize(),
            connectedProviders: $payload['providers'],
            badges: $badges,
            pastSeasons: $pastSeasons
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'information' => $this->informationEntity,
            'address' => $this->addressEntity,
            'character' => $this->characterEntity,
            'connectedProviders' => $this->connectedProviders,
            'badges' => $this->badges,
            'pastSeasons' => $this->pastSeasons,
        ];
    }
}
