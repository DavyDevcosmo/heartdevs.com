<?php

declare(strict_types=1);

namespace Heart\Core\Providers;

use Heart\Core\Classes\DomainManager;
use Illuminate\Support\ServiceProvider;

final class CoreProvider extends ServiceProvider
{
    public function register(): void
    {
        $providers = DomainManager::instance()->getproviders();
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
