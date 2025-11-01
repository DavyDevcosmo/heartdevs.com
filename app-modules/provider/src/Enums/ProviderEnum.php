<?php

declare(strict_types=1);

namespace Heart\Provider\Enums;

enum ProviderEnum: string
{
    case Discord = 'discord';
    case Twitch = 'twitch';
}
