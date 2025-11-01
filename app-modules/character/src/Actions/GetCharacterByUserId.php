<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Entities\CharacterEntity;

final readonly class GetCharacterByUserId
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $userId): CharacterEntity
    {
        return $this->characterRepository->findByUserId($userId);
    }
}
