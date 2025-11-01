<?php

declare(strict_types=1);

namespace He4rt\Feedback\Actions;

use He4rt\Feedback\Entities\FeedbackEntity;
use He4rt\Feedback\Contracts\FeedbackRepository;

final readonly class GetFeedbackById
{
    public function __construct(private FeedbackRepository $repository) {}

    public function handle(string $id): FeedbackEntity
    {
        return $this->repository->findById($id);
    }
}
