<?php

declare(strict_types=1);

namespace Heart\Feedback\Domain\Actions;

use Heart\Feedback\Domain\DTOs\NewFeedbackDTO;
use Heart\Feedback\Domain\Entities\FeedbackEntity;
use Heart\Feedback\Domain\Repositories\FeedbackRepository;
use Heart\Provider\Actions\FindProvider;
use Heart\Provider\Enums\ProviderEnum;

final readonly class CreateFeedback
{
    public function __construct(
        private FeedbackRepository $feedbackRepository,
        private FindProvider $findProvider
    ) {}

    public function handle(array $payload): FeedbackEntity
    {
        $payload = $this->transformPeopleInvolvedIds($payload);
        $newFeedbackDTO = NewFeedbackDTO::make($payload);

        return $this->feedbackRepository->create($newFeedbackDTO);
    }

    private function transformPeopleInvolvedIds(array $payload): array
    {
        $senderUserEntity = $this->findProvider->handle(ProviderEnum::Discord->value, $payload['sender_id']);
        $targetUserEntity = $this->findProvider->handle(ProviderEnum::Discord->value, $payload['target_id']);

        $payload['sender_id'] = $senderUserEntity->userId;
        $payload['target_id'] = $targetUserEntity->userId;

        return $payload;
    }
}
