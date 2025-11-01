<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use src\Entities\CharacterEntity;
use src\Repositories\CharacterRepository;

final readonly class FindCharacter
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $characterId): CharacterEntity
    {
        return $this->characterRepository->findById($characterId);
    }
}
