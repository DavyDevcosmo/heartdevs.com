<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use He4rt\Meeting\Contracts\MeetingRepository;

final readonly class PersistAttendMeetingAction
{
    public function __construct(private MeetingRepository $meetingRepository) {}

    public function handle(string $meetingId, string $userId): void
    {
        $this->meetingRepository->attendMeeting($meetingId, $userId);
    }
}
