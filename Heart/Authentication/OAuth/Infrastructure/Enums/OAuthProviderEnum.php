<?php

declare(strict_types=1);

namespace Heart\Authentication\OAuth\Infrastructure\Enums;

use Heart\Authentication\OAuth\Domain\Interfaces\OAuthClientContract;
use src\Twitch\OAuth\Domain\TwitchOAuthService;

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
