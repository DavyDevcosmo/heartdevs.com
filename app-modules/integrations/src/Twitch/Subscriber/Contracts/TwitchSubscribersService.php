<?php

declare(strict_types=1);

namespace He4rt\Integrations\Twitch\Subscriber\Contracts;

use Heart\Autentication\DTO\OAuthAccessDTO;

interface TwitchSubscribersService
{
    public function getSubscriptionState(OAuthAccessDTO $dto, string $twitchId, string $channelId);
}
