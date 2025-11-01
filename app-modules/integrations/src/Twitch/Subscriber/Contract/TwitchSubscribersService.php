<?php

declare(strict_types=1);

namespace Heart\Integrations\Twitch\Subscriber\Contract;

use Heart\Authentication\OAuth\Domain\DTO\OAuthAccessDTO;

interface TwitchSubscribersService
{
    public function getSubscriptionState(OAuthAccessDTO $dto, string $twitchId, string $channelId);
}
