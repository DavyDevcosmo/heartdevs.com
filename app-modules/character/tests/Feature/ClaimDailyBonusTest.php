<?php

declare(strict_types=1);

use He4rt\Character\Models\Character;
use He4rt\Provider\Models\Provider;
use He4rt\User\Models\User;
use Symfony\Component\HttpFoundation\Response;

test('success', function (): void {
    $user = User::factory()
        ->has(Provider::factory(), 'providers')
        ->has(Character::factory(), 'character')
        ->create();

    $provider = $user->providers[0];
    $routeParams = [
        'provider' => $provider->provider,
        'providerId' => $provider->provider_id,
    ];
    $expected = $user->character->daily_bonus_claimed_at;
    $this->travelTo(now()->addHours(24)->addMinutes(2));
    $this
        ->actingAsAdmin()
        ->postJson(route('characters.dailyReward', $routeParams))
        ->assertStatus(Response::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('characters', [
        'daily_bonus_claimed_at' => $expected,
    ]);
});

test('should not claim before24 hours', function (): void {
    $user = User::factory()
        ->has(Provider::factory(), 'providers')
        ->has(Character::factory(), 'character')
        ->create();

    $provider = $user->providers[0];
    $routeParams = [
        'provider' => $provider->provider,
        'providerId' => $provider->provider_id,
    ];

    $this
        ->actingAsAdmin()
        ->postJson(route('characters.dailyReward', $routeParams))
        ->assertStatus(Response::HTTP_FORBIDDEN);
});
