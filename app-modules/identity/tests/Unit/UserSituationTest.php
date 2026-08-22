<?php

declare(strict_types=1);

use He4rt\Identity\User\Enums\UserSituation;
use He4rt\Identity\User\Models\User;

test('banned_at preenchido devolve Banned', function (): void {
    $user = User::factory()->create(['banned_at' => now()->subDay()]);

    expect($user->situation)->toBe(UserSituation::Banned);
});

test('suspensão vigente devolve Suspended', function (): void {
    $user = User::factory()->create(['suspended_until' => now()->addWeek()]);

    expect($user->situation)->toBe(UserSituation::Suspended);
});

test('suspensão vencida devolve Active', function (): void {
    $user = User::factory()->create(['suspended_until' => now()->subDay()]);

    expect($user->situation)->toBe(UserSituation::Active);
});

test('sem punição devolve Active', function (): void {
    $user = User::factory()->create();

    expect($user->situation)->toBe(UserSituation::Active);
});

test('banimento vence suspensão quando os dois estão preenchidos', function (): void {
    $user = User::factory()->create([
        'banned_at' => now()->subDay(),
        'suspended_until' => now()->addWeek(),
    ]);

    expect($user->situation)->toBe(UserSituation::Banned);
});
