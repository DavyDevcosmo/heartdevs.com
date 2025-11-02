<?php

declare(strict_types=1);

namespace He4rt\Message\Contracts;

use He4rt\Message\DTO\NewVoiceMessageDTO;
use He4rt\Message\Entities\VoiceEntity;

interface VoiceRepository
{
    public function create(NewVoiceMessageDTO $messageDTO, string $providerId, int $obtainedExperience): VoiceEntity;
}
