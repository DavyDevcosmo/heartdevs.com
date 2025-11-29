<?php

declare(strict_types=1);

namespace App\Listeners;

use function Illuminate\Log\log;
use GhostZero\Tmi\Events\Twitch\CheerEvent;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use GhostZero\Tmi\Events\Twitch\SubEvent;
use He4rt\Message\Actions\NewMessage;
use He4rt\Message\DTO\NewMessageDTO;
use He4rt\Provider\Enums\ProviderEnum;
use He4rt\Provider\Models\Provider;

class TmiEventSubscriber
{
    public function handleMessageEvent(MessageEvent $event): void
    {

        log($event->channel->getName());

        $tenant = Provider::query()
            ->where('provider', ProviderEnum::Twitch)
            ->where('provider_id', $event->channel->getName())
            ->first();

        dump($event->tags);

        app(NewMessage::class)
            ->persist(new NewMessageDTO(
                tenantId: $tenant->tenant_id,
                provider: ProviderEnum::Twitch,
                providerUsername: $event->user,
                providerId: $event->tags->offsetGet('user-id'),
                providerMessageId: $event->tags->offsetGet('id'),
                channelId: $event->channel->getName(),
                content: $event->message,
                sentAt: now()->toDateTimeImmutable()
            ));
    }

    public function handleCheerEvent(CheerEvent $event): void
    {
        // handle your cheer event
    }

    public function handleSubEvent(SubEvent $event): void
    {
        // handle your sub event
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(): array
    {
        return [
            MessageEvent::class => [
                self::handleMessageEvent(...),
            ],
            CheerEvent::class => [
                self::handleMessageEvent(...),
            ],
            SubEvent::class => [
                self::handleMessageEvent(...),
            ],
        ];
    }
}
