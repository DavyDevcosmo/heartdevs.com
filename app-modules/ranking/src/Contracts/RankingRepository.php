<?php

declare(strict_types=1);

namespace He4rt\Ranking\Contracts;

use He4rt\Shared\Contract\Paginator;

interface RankingRepository
{
    public function rankingByLevel(): Paginator;
}
