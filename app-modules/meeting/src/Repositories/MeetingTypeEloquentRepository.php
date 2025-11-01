<?php

declare(strict_types=1);

namespace He4rt\Meeting\Repositories;

use He4rt\Meeting\Contracts\MeetingTypeRepository;
use He4rt\Meeting\Entities\MeetingTypeEntity;
use He4rt\Meeting\Models\MeetingType;

final readonly class MeetingTypeEloquentRepository implements MeetingTypeRepository
{
    public function __construct(private MeetingType $model) {}

    public function findById(int $meetingTypeId): ?MeetingTypeEntity
    {
        /** @var MeetingType $model */
        $model = $this->model->find($meetingTypeId);

        if (! $model) {
            return null;
        }

        return MeetingTypeEntity::make($model->toArray());
    }
}
