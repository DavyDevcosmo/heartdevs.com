<?php

declare(strict_types=1);

namespace He4rt\Authentication\Providers;

use Heart\Provider\Domain\Repositories\ProviderRepository;
use Heart\Provider\Domain\Repositories\TokenRepository;
use Heart\Provider\Infrastructure\Repositories\ProviderEloquentRepository;
use Heart\Provider\Infrastructure\Repositories\TokenEloquentRepository;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProviderRepository::class, ProviderEloquentRepository::class);
        $this->app->bind(TokenRepository::class, TokenEloquentRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/authentication-routes.php');
    }
}
