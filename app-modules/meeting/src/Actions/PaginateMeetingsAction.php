<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Shared\Contract\Paginator;

final readonly class PaginateMeetingsAction
{
    public function __construct(private MeetingRepository $repository) {}

    public function handle(): Paginator
    {
        return $this->repository->paginate(['meetingType']);
    }
}
