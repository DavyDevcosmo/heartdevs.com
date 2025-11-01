<?php

declare(strict_types=1);

namespace He4rt\Message\Actions;

use He4rt\Message\Contracts\MessageRepository;
use He4rt\Message\DTO\NewMessageDTO;
use He4rt\Message\Entities\MessageEntity;

class PersistMessage
{
    public function __construct(
        private readonly MessageRepository $messageRepository
    ) {}

    public function handle(
        NewMessageDTO $messageDTO,
        int $obtainedExperience,
        string $providerEntity
    ): MessageEntity {
        return $this->messageRepository->create($messageDTO, $providerEntity, $obtainedExperience);
    }
}
