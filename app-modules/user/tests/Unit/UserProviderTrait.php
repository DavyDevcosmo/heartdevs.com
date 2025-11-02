<?php

declare(strict_types=1);

namespace He4rt\User\Tests\Unit;

use He4rt\User\Entities\UserEntity;
use He4rt\User\ValueObjects\UserName;

trait UserProviderTrait
{
    public function validUserPayload(array $fields = []): array
    {
        return [
            'id' => '12',
            'username' => new UserName('canhassi'),
            'isDonator' => false,
            ...$fields,
        ];
    }

    public function validUserEntity(): UserEntity
    {
        return UserEntity::make($this->validUserPayload());
    }
}
