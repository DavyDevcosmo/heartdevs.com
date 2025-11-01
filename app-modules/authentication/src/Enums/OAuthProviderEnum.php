<?php

declare(strict_types=1);

namespace He4rt\Authentication\Enums;

use He4rt\Authentication\Contracts\OAuthClientContract;
use He4rt\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;

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
