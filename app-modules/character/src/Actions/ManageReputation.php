<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use He4rt\Character\Contracts\CharacterRepository;

final readonly class ManageReputation
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $characterId, string $type): void
    {
        $character = $this->characterRepository->findById($characterId);
        $character->reputation->handleReputation($type);

        $this->characterRepository->updateReputation($character);
    }
}
