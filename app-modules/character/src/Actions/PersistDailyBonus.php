<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Exceptions\CharacterException;

final readonly class PersistDailyBonus
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $characterId): void
    {
        $character = $this->characterRepository->findById($characterId);
        throw_unless($character->dailyReward->canClaim(), CharacterException::alreadyClaimed($character->dailyReward));

        $this->characterRepository->claimDailyBonus($character);
    }
}
