<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\DTO\NewMeetingDTO;
use He4rt\Meeting\Entities\MeetingEntity;

final readonly class CreateMeetingAction
{
    public function __construct(private MeetingRepository $repository) {}

    public function handle(NewMeetingDTO $dto, string $adminId): MeetingEntity
    {
        return $this->repository->create($dto, $adminId);
    }
}
