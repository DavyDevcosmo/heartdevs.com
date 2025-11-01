<?php

declare(strict_types=1);

namespace He4rt\Feedback\Contracts;

use He4rt\Feedback\DTO\FeedbackReviewDTO;
use He4rt\Feedback\DTO\NewFeedbackDTO;
use He4rt\Feedback\Entities\FeedbackEntity;

interface FeedbackRepository
{
    public function create(NewFeedbackDTO $dto): FeedbackEntity;

    public function reviewFeedback(FeedbackReviewDTO $dto): void;

    public function findById(string $id): FeedbackEntity;
}
