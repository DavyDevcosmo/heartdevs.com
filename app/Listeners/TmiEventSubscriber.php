<?php

declare(strict_types=1);

namespace App\Listeners;

use GhostZero\Tmi\Events\Twitch\CheerEvent;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use GhostZero\Tmi\Events\Twitch\SubEvent;

class TmiEventSubscriber
{
    public function handleMessageEvent(MessageEvent $event): void {}

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
