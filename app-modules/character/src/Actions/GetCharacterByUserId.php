<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use src\Entities\CharacterEntity;
use src\Repositories\CharacterRepository;

final readonly class GetCharacterByUserId
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $userId): CharacterEntity
    {
        return $this->characterRepository->findByUserId($userId);
    }
}
