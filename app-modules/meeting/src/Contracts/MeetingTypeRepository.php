<?php

declare(strict_types=1);

namespace He4rt\Meeting\Contracts;

use He4rt\Meeting\Entities\MeetingTypeEntity;

interface MeetingTypeRepository
{
    public function findById(int $meetingTypeId): ?MeetingTypeEntity;
}
