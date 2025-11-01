<?php

declare(strict_types=1);

namespace He4rt\Ranking\Repositories;

use Heart\Character\Infrastructure\Models\Character;
use He4rt\Ranking\Contracts\RankingRepository;
use Heart\Shared\Domain\Paginator;
use Heart\Shared\Infrastructure\Paginator as PaginatorConcrete;

final class RankingEloquentRepository implements RankingRepository
{
    public function rankingByLevel(): Paginator
    {
        $ranking = Character::with(['user'])
            ->orderByDesc('experience')
            ->paginate(10);

        return PaginatorConcrete::paginate($ranking);
    }
}
