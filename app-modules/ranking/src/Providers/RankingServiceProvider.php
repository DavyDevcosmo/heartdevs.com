<?php

declare(strict_types=1);

namespace He4rt\Ranking\Providers;

use He4rt\Ranking\Contracts\RankingRepository;
use He4rt\Ranking\Repositories\RankingEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RankingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RankingRepository::class, RankingEloquentRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/ranking-routes.php');
    }
}
