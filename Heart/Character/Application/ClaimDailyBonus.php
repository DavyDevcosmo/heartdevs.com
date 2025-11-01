<?php

declare(strict_types=1);

namespace Heart\Character\Application;

use Heart\Character\Domain\Actions\PersistDailyBonus;
use Heart\Provider\Actions\FindProvider;

final readonly class ClaimDailyBonus
{
    public function __construct(
        private PersistDailyBonus $dailyBonus,
        private FindProvider $findProvider,
        private FindCharacterIdByUserId $findCharacter,
    ) {}

    public function handle(string $provider, string $providerId): void
    {
        $providerEntity = $this->findProvider->handle($provider, $providerId);

        $characterId = $this->findCharacter->handle($providerEntity->userId);

        $this->dailyBonus->handle($characterId);
    }
}
