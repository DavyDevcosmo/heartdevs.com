<?php

declare(strict_types=1);

namespace He4rt\Feedback\Actions;

use He4rt\Feedback\DTO\FeedbackReviewDTO;
use He4rt\Provider\Actions\FindProvider;
use He4rt\Provider\Enums\ProviderEnum;

final readonly class ReviewFeedback
{
    public function __construct(
        private PersistFeedbackReview $persistReview,
        private FindProvider $findProvider,
    ) {}

    public function handle(
        string $feedbackId,
        string $reviewType,
        string $providerAdminId,
        ?string $reason = null
    ): void {
        $providerEntity = $this->findProvider->handle(ProviderEnum::Discord->value, $providerAdminId);
        $reviewDTO = FeedbackReviewDTO::make($feedbackId, $reviewType, $providerEntity, $reason);

        $this->persistReview->handle($reviewDTO);
    }
}
