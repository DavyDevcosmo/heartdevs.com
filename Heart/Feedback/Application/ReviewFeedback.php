<?php

declare(strict_types=1);

namespace Heart\Feedback\Application;

use Heart\Feedback\Domain\Actions\PersistFeedbackReview;
use Heart\Feedback\Domain\DTOs\FeedbackReviewDTO;
use Heart\Provider\Actions\FindProvider;
use Heart\Provider\Enums\ProviderEnum;

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
