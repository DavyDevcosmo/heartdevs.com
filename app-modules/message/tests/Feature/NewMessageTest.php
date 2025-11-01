<?php

declare(strict_types=1);

use He4rt\Provider\Model\Provider;
use Heart\User\Infrastructure\Models\User;
use Illuminate\Support\Facades\Cache;
use src\Models\Character;
use src\Models\Meeting;

test('can create amessage', function (): void {
    Cache::tags(['meetings'])->flush();

    $user = User::factory()
        ->has(Character::factory(['experience' => 1]), 'character')
        ->has(Provider::factory(), 'providers')
        ->create();
    $provider = $user->providers[0];
    $payload = [
        'provider' => $provider->provider,
        'provider_id' => $provider->provider_id,
        'provider_message_id' => '12312312',
        'channel_id' => '312321',
        'content' => '321312',
        'sent_at' => now()->toDateTimeString(),
    ];

    $this
        ->actingAsAdmin()
        ->postJson(route('messages.create', ['provider' => $provider->provider]), $payload)
        ->assertNoContent();

    $this->assertDatabaseMissing('characters', [
        'user_id' => $user->getKey(),
        'experience' => 1,
    ]);
});

test('can create amessage with level zero', function (): void {
    Cache::tags(['meetings'])->flush();

    $user = User::factory()
        ->has(Character::factory(['experience' => 0]), 'character')
        ->has(Provider::factory(), 'providers')
        ->create();
    $provider = $user->providers[0];
    $payload = [
        'provider' => $provider->provider,
        'provider_id' => $provider->provider_id,
        'provider_message_id' => '12312312',
        'channel_id' => '312321',
        'content' => '321312',
        'sent_at' => now()->toDateTimeString(),
    ];

    $this
        ->actingAsAdmin()
        ->postJson(route('messages.create', ['provider' => $provider->provider]), $payload)
        ->assertNoContent();

    $this->assertDatabaseMissing('characters', [
        'user_id' => $user->getKey(),
        'experience' => 1,
    ]);
});

test('can create amessage and receive ameeting check', function (): void {
    Cache::tags(['meetings'])->flush();

    $user = User::factory()
        ->has(Character::factory(['experience' => 1]), 'character')
        ->has(Provider::factory(), 'providers')
        ->create();

    $meeting = Meeting::factory()
        ->unfinished()
        ->create();

    Cache::tags(['meetings'])->put('current-meeting', $meeting->id);

    $provider = $user->providers[0];
    $payload = [
        'provider' => $provider->provider,
        'provider_id' => $provider->provider_id,
        'provider_message_id' => '12312312',
        'channel_id' => '312321',
        'content' => '321312',
        'sent_at' => now()->toDateTimeString(),
    ];

    $this
        ->actingAsAdmin()
        ->postJson(route('messages.create', ['provider' => $provider->provider]), $payload)
        ->assertNoContent();

    $this->assertDatabaseMissing('characters', [
        'user_id' => $user->getKey(),
        'experience' => 1,
    ]);

    $this->assertDatabaseHas('meeting_participants', [
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
    ]);
    $userAttendedCacheKey = sprintf('meeting-%s-attended', $user->id);
    expect(Cache::tags(['meetings'])->has($userAttendedCacheKey))->toBeTrue();
    Cache::tags(['meetings'])->flush();

    expect(Cache::tags(['meetings'])->has($userAttendedCacheKey))->toBeFalse();
});
