<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Entities\CharacterEntity;

final readonly class FindCharacter
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $characterId): CharacterEntity
    {
        return $this->characterRepository->findById($characterId);
    }
}
