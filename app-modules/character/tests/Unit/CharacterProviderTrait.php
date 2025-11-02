<?php

declare(strict_types=1);

namespace He4rt\Character\Tests\Unit;

use He4rt\Character\Entities\CharacterEntity;

trait CharacterProviderTrait
{
    public function validCharacterPayload(array $fields = []): array
    {
        return [
            'id' => '1',
            'user_id' => '1',
            'experience' => 500,
            'reputation' => 1,
            'daily_bonus_claimed_at' => now()->format('Y-m-d H:i:s'),
            ...$fields,
        ];
    }

    public function validCharacterEntity(array $fields = []): CharacterEntity
    {
        return CharacterEntity::make($this->validCharacterPayload($fields));
    }
}
