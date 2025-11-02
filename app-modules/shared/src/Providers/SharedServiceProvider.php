<?php

declare(strict_types=1);

namespace He4rt\Shared\Providers;

use He4rt\Shared\Contract\Paginator as PaginatorInterface;
use He4rt\Shared\Paginator;
use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaginatorInterface::class, Paginator::class);
    }
}
