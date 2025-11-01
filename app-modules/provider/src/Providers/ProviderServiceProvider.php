<?php

declare(strict_types=1);

namespace He4rt\Provider\Providers;

use Heart\Provider\Contracts\ProviderRepository;
use Heart\Provider\Repositories\ProviderEloquentRepository;
use Illuminate\Support\ServiceProvider;

class ProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProviderRepository::class, ProviderEloquentRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/provider-routes.php');
    }
}
