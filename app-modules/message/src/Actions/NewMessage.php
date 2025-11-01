<?php

declare(strict_types=1);

namespace He4rt\Message\Actions;

use He4rt\Meeting\Actions\AttendMeeting;
use He4rt\Message\DTOs\NewMessageDTO;
use Heart\Character\Application\FindCharacterIdByUserId;
use Heart\Character\Domain\Actions\IncrementExperience;
use Heart\Provider\Application\FindProvider;
use Heart\Provider\Application\NewAccountByProvider;
use Heart\Provider\Domain\Entities\ProviderEntity;
use Heart\Provider\Infrastructure\Exceptions\ProviderException;
use Illuminate\Support\Facades\Cache;

final readonly class NewMessage
{
    public function __construct(
        private PersistMessage $persistMessage,
        private FindProvider $findProvider,
        private FindCharacterIdByUserId $findCharacterId,
        private IncrementExperience $characterExperience,
        private AttendMeeting $attendMeeting,
        private NewAccountByProvider $createAccount
    ) {}

    public function persist(array $payload): void
    {
        $messageDTO = NewMessageDTO::make($payload);
        try {
            $providerEntity = $this->findProvider->handle(
                $messageDTO->provider->value,
                $messageDTO->providerId
            );

        } catch (ProviderException) {
            $providerEntity = $this->createAccount->handle(
                $messageDTO->provider,
                $messageDTO->providerId,
                'discord-'.$messageDTO->providerId
            );
        }

        $obtainedExperience = $this->persistCharacterExperience(
            $providerEntity->userId,
            $messageDTO->content
        );

        $this->persistMessage->handle(
            $messageDTO,
            $obtainedExperience,
            $providerEntity->id,
        );

        $this->meetingAttender($providerEntity);
    }

    private function persistCharacterExperience(string $userId, string $content): int
    {
        $characterId = $this->findCharacterId->handle($userId);

        return $this->characterExperience->incrementByTextMessage($characterId, $content);
    }

    private function meetingAttender(ProviderEntity $providerEntity): void
    {
        if (! Cache::tags(['meetings'])->has('current-meeting')) {
            return;
        }

        $userAttendedCacheKey = sprintf('meeting-%s-attended', $providerEntity->userId);
        if (Cache::tags(['meetings'])->has($userAttendedCacheKey)) {
            return;
        }

        $this->attendMeeting->handle($providerEntity->userId);
    }
}
