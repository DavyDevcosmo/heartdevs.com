<?php

declare(strict_types=1);

use He4rt\Character\Entities\CharacterEntity;

dataset('character provider', fn () => [
    ['1', '1', 1, 548, '2023-01-14 00:26:25', 4],
    ['1', '1', 1, 89, '2023-01-14 00:26:25', 1],
    ['1', '1', 1, 287, '2023-01-14 00:26:25', 3],
]);

dataset('make character provider', fn () => [
    [['id' => '1', 'user_id' => '1', 'reputation' => 1, 'experience' => 548, 'daily_bonus_claimed_at' => '2023-01-14 00:26:25'], 4],
    [['id' => '1', 'user_id' => '1', 'reputation' => 1, 'experience' => 98, 'daily_bonus_claimed_at' => '2023-01-14 00:26:25'], 2],
]);

test('instance character entity test', function (
    string $id, string $userId, int $reputation, int $experience, string $claimedAt, int $expectedLevel
): void {
    $characterEntity = new CharacterEntity($id, $userId, $reputation, $experience, $claimedAt);

    expect($expectedLevel)->toBe($characterEntity->getLevel())
        ->and($characterEntity)->toBeInstanceOf(CharacterEntity::class);
})->with('character provider');

test('make character', function (array $payload, int $expectedLevel): void {
    $characterEntity = CharacterEntity::make($payload);

    expect($expectedLevel)->toBe($characterEntity->getLevel())
        ->and($characterEntity)->toBeInstanceOf(CharacterEntity::class);
})->with('make character provider');
