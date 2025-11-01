<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use src\Repositories\CharacterRepository;

final readonly class PersistClaimedBadge
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(string $characterId, int $badgeId): void
    {
        $this->characterRepository->claimBadge($characterId, $badgeId);
    }
}
