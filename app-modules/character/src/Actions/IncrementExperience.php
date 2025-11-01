<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use src\Enums\VoiceStatesEnum;
use src\Repositories\CharacterRepository;

class IncrementExperience
{
    public function __construct(
        private readonly CharacterRepository $characterRepository,
        private readonly FindCharacter $findCharacter
    ) {}

    public function incrementByTextMessage(string $characterId, string $message): int
    {
        $characterEntity = $this->findCharacter->handle($characterId);
        $experienceObtained = $characterEntity->level->generateExperience($message);
        $this->characterRepository->updateExperience($characterEntity);

        return $experienceObtained;
    }

    public function incrementByVoiceMessage(string $characterId, VoiceStatesEnum $voiceState): int
    {
        $characterEntity = $this->findCharacter->handle($characterId);
        $experienceObtained = $characterEntity->level->generateVoiceExperience($voiceState);
        $this->characterRepository->updateExperience($characterEntity);

        return $experienceObtained;
    }
}
