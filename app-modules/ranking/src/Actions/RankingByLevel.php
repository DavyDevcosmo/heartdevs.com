<?php

declare(strict_types=1);

namespace Heart\Ranking\Actions;

use Heart\Ranking\Contracts\RankingRepository;
use Heart\Shared\Domain\Paginator;

final readonly class RankingByLevel
{
    public function __construct(
        private RankingRepository $rankingRepository
    ) {}

    public function handle(): Paginator
    {
        return $this->rankingRepository->rankingByLevel();
    }
}
