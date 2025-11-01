<?php

declare(strict_types=1);

namespace He4rt\Message\Contracts;

use He4rt\Message\DTO\NewMessageDTO;
use He4rt\Message\Entities\MessageEntity;

interface MessageRepository
{
    public function create(NewMessageDTO $payload, string $providerId, int $obtainedExperience): MessageEntity;
}
