<?php

declare(strict_types=1);

use He4rt\Provider\Model\Provider;
use Heart\User\Infrastructure\Models\User;
use src\Enums\VoiceStatesEnum;
use src\Models\Character;

test('can create voice message', function (): void {
    config(['he4rt.season.id' => 2]);

    $user = User::factory()
        ->has(Character::factory(['experience' => 1]), 'character')
        ->has(Provider::factory(), 'providers')
        ->create();

    $provider = $user->providers[0];
    $payload = [
        'provider' => $provider->provider,
        'provider_id' => $provider->provider_id,
        'state' => VoiceStatesEnum::Muted->value,
        'channel_name' => 'Estudando',
    ];

    $this->actingAsAdmin()
        ->post(route('voices.create', $provider->provider), $payload)
        ->assertNoContent();

    $this->assertDatabaseMissing('characters', [
        'user_id' => $user->getKey(),
        'experience' => 1,
    ]);

    $this->assertDatabaseHas('voice_messages', [
        'state' => $payload['state'],
        'channel_name' => $payload['channel_name'],
        'season_id' => 2,
    ]);
});
