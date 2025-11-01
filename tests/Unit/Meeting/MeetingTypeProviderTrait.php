<?php

declare(strict_types=1);

namespace Tests\Unit\Meeting;

use Illuminate\Support\Facades\Date;
use src\Entities\MeetingTypeEntity;

trait MeetingTypeProviderTrait
{
    public function validMeetingPayload(array $fields = []): array
    {
        return [
            'id' => 12,
            'name' => 'canhassi',
            'week_day' => 1,
            'start_at' => Date::now(),
        ];
    }

    public function validMeetingTypeEntity(): MeetingTypeEntity
    {
        return MeetingTypeEntity::make($this->validMeetingPayload());
    }
}
