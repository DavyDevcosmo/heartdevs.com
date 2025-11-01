<?php

declare(strict_types=1);

namespace He4rt\Integrations\Providers;

use Heart\Integrations\Common\Client\TwitchBaseClient;
use Heart\Integrations\Common\Contracts\TwitchService;
use Heart\Integrations\Twitch\OAuth\Client\TwitchOAuthClient;
use Heart\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;
use Illuminate\Support\ServiceProvider;

class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TwitchService::class, TwitchBaseClient::class);
        $this->app->bind(TwitchOAuthService::class, TwitchOAuthClient::class);
    }

    public function boot(): void {}
}
