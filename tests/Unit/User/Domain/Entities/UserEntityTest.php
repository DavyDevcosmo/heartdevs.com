<?php

declare(strict_types=1);
use Heart\User\Domain\Entities\UserEntity;
use PHPUnit\Framework\Attributes\DataProvider;
dataset('validUserPayloads', function () {
    return [
        [[
            'id' => 123,
            'name' => 'Luis Alberto Suárez',
            'username' => 'brabo3k',
            'is_donator' => true,
        ]],
        [[
            'id' => 1,
            'name' => 'Diego Souza',
            'username' => 'brabo4k',
            'is_donator' => false,
        ]],
    ];
});
test('can create entity', function (array $userPayload) {
    $user = UserEntity::fromArray($userPayload);

    expect($user)->toBeInstanceOf(UserEntity::class);
})->with('validUserPayloads');
