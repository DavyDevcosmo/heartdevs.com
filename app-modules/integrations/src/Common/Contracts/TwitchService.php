<?php

declare(strict_types=1);

namespace Heart\Integrations\Common\Contracts;

use Heart\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;
use Heart\Integrations\Twitch\Subscriber\Contract\TwitchSubscribersService;

interface TwitchService
{
    public function oauth(): TwitchOAuthService;

    public function subscribers(): TwitchSubscribersService;
}
