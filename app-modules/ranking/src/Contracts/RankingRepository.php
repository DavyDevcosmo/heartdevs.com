<?php

declare(strict_types=1);

namespace He4rt\Ranking\Contracts;

use Heart\Shared\Domain\Paginator;

interface RankingRepository
{
    public function rankingByLevel(): Paginator;
}
