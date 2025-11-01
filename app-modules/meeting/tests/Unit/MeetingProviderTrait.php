<?php

declare(strict_types=1);

namespace He4rt\Meeting\Tests\Unit;

use He4rt\Meeting\Entities\MeetingEntity;
use Illuminate\Support\Facades\Date;

trait MeetingProviderTrait
{
    public function validMeetingPayload(array $fields = []): array
    {
        return [
            'id' => 12,
            'content' => null,
            'meeting_type_id' => 12,
            'admin_id' => '12',
            'starts_at' => $time = Date::now(),
            'ends_at' => $time->addMinutes(12)->toString(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];
    }

    public function validMeetingEntity(): MeetingEntity
    {
        return MeetingEntity::make($this->validMeetingPayload());
    }
}
