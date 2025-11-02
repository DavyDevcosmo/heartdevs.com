<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\Entities\MeetingEntity;

final readonly class FinishMeetingAction
{
    public function __construct(private MeetingRepository $meetingRepository) {}

    public function handle(string $meetingId): MeetingEntity
    {
        return $this->meetingRepository->endMeeting($meetingId);
    }
}
