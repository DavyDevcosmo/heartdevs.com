<?php

declare(strict_types=1);

namespace Heart\Feedback\Domain\DTOs;

use Heart\Feedback\Domain\Enums\ReviewTypeEnum;
use Heart\Provider\Entities\ProviderEntity;
use JsonSerializable;

final readonly class FeedbackReviewDTO implements JsonSerializable
{
    public function __construct(
        public string $feedbackId,
        public ReviewTypeEnum $reviewTypeEnum,
        public ProviderEntity $adminProviderEntity,
        public ?string $reason,
    ) {}

    public static function make(
        string $feedbackId,
        string $reviewType,
        ProviderEntity $providerEntity,
        ?string $reason
    ): self {
        return new self(
            feedbackId: $feedbackId,
            reviewTypeEnum: ReviewTypeEnum::from($reviewType),
            adminProviderEntity: $providerEntity,
            reason: $reason
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'feedback_id' => $this->feedbackId,
            'staff_id' => $this->adminProviderEntity->userId,
            'status' => $this->reviewTypeEnum->value,
            'reason' => $this->reason,
            'received_at' => $this->reason,
        ];
    }
}
