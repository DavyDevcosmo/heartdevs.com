<?php

declare(strict_types=1);

namespace He4rt\Ranking\Repositories;

use He4rt\Character\Models\Character;
use He4rt\Ranking\Contracts\RankingRepository;
use He4rt\Shared\Contract\Paginator;
use He4rt\Shared\Paginator as PaginatorConcrete;

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
