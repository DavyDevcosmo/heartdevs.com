<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use He4rt\Meeting\Contracts\MeetingTypeRepository;
use He4rt\Meeting\Entities\MeetingTypeEntity;
use He4rt\Meeting\Exceptions\MeetingException;
use Throwable;

final readonly class FindMeetingTypeAction
{
    public function __construct(private MeetingTypeRepository $meetingTypeRepository) {}

    /**
     * @throws Throwable
     */
    public function handle(int $meetingType): MeetingTypeEntity
    {
        $meetingTypeEntity = $this->meetingTypeRepository->findById($meetingType);

        throw_unless($meetingTypeEntity instanceof MeetingTypeEntity, MeetingException::meetingTypeNotFound());

        return $meetingTypeEntity;
    }
}
