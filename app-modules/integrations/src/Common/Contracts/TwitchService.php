<?php

declare(strict_types=1);

namespace He4rt\Integrations\Common\Contracts;

use He4rt\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;
use He4rt\Integrations\Twitch\Subscriber\Contracts\TwitchSubscribersService;

interface TwitchService
{
    public function oauth(): TwitchOAuthService;

    public function subscribers(): TwitchSubscribersService;
}
