<?php

declare(strict_types=1);

namespace Heart\Integrations\Common\Client;

use GuzzleHttp\Client;
use Heart\Integrations\Common\Contracts\TwitchService;
use Heart\Integrations\Twitch\OAuth\Client\TwitchOAuthClient;
use Heart\Integrations\Twitch\OAuth\Contracts\TwitchOAuthService;
use Heart\Integrations\Twitch\Subscriber\Contract\TwitchSubscribersService;

final readonly class TwitchBaseClient implements TwitchService
{
    public function __construct(private Client $client) {}

    public function oauth(): TwitchOAuthService
    {
        return new TwitchOAuthClient($this->client);
    }

    public function subscribers(): TwitchSubscribersService
    {
        return new TwitchSubscribersClient($this->client);
    }
}
