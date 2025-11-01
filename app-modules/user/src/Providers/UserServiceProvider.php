<?php

declare(strict_types=1);

namespace He4rt\User\Providers;

use He4rt\User\Contracts\UserRepository;
use He4rt\User\Repositories\UserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, UserEloquentRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/user-routes.php');
    }
}
