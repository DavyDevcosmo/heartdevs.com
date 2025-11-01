<?php

declare(strict_types=1);

namespace Heart\Authentication\Enums;

use Heart\Authentication\Contracts\OAuthClientContract;
use Heart\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;


enum OAuthProviderEnum: string
{
    case Twitch = 'twitch';

    public function getProvider(): OAuthClientContract
    {
        return match ($this) {
            self::Twitch => app(TwitchOAuthService::class)
        };
    }
}
