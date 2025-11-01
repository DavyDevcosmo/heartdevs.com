<?php

declare(strict_types=1);

namespace Heart\Integrations\Twitch\Subscriber\Client;

use GuzzleHttp\Client;
use Heart\Authentication\OAuth\Domain\DTO\OAuthAccessDTO;
use Heart\Integrations\Twitch\Subscriber\Contracts\TwitchSubscribersService;
use Heart\Integrations\Twitch\Subscriber\DTO\TwitchSubscriberDTO;

final readonly class TwitchSubscribersClient implements TwitchSubscribersService
{
    public function __construct(private Client $client) {}

    public function getSubscriptionState(OAuthAccessDTO $dto, string $twitchId, string $channelId): ?TwitchSubscriberDTO
    {
        $uri = 'https://api.twitch.tv/helix/subscriptions/user';
        $response = $this->client->request('GET', $uri, [
            'headers' => [
                'Client-ID' => config('kingdom.integrations.twitch.client_id'),
                'Authorization' => 'Bearer '.$dto->accessToken,
            ],
            'query' => [
                'user_id' => $twitchId,
                'broadcaster_id' => $channelId,
            ],
        ]);

        $response = json_decode($response->getBody()->getContents(), true)['data'];

        if (count($response) === 0) {
            return null;
        }

        return TwitchSubscriberDTO::make($response[0]);
    }
}
