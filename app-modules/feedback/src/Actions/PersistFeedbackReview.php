<?php

declare(strict_types=1);

namespace He4rt\Feedback\Actions;

use He4rt\Feedback\DTO\FeedbackReviewDTO;
use He4rt\Feedback\Contracts\FeedbackRepository;

final readonly class PersistFeedbackReview
{
    public function __construct(private FeedbackRepository $feedbackRepository) {}

    public function handle(FeedbackReviewDTO $feedbackReviewDTO): void
    {
        $this->feedbackRepository->reviewFeedback($feedbackReviewDTO);
    }
}
