<?php

declare(strict_types=1);

namespace He4rt\Authentication\Providers;

use He4rt\Provider\Contracts\ProviderRepository;
use He4rt\Provider\Contracts\TokenRepository;
use He4rt\Provider\Repositories\ProviderEloquentRepository;
use He4rt\Provider\Repositories\TokenEloquentRepository;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProviderRepository::class, ProviderEloquentRepository::class);
        $this->app->bind(TokenRepository::class, TokenEloquentRepository::class);
    }

    public function boot(): void {}
}
