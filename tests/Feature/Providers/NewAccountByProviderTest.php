<?php

declare(strict_types=1);
use Heart\Provider\Infrastructure\Models\Provider;
use Symfony\Component\HttpFoundation\Response;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('can create account by provider', function () {
    $provider = 'discord';
    $payload = [
        'provider_id' => '184789120940244992',
        'username' => 'danielhe4rt',
    ];

    $response = $this
        ->actingAsAdmin()
        ->postJson(route('providers.store', ['provider' => $provider]), $payload);

    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('users', [
        'username' => $payload['username'],
    ]);

    $this->assertDatabaseHas('providers', [
        'provider' => $provider,
        'provider_id' => $payload['provider_id'],
    ]);

    $this->assertDatabaseHas('characters', [
        'user_id' => $response['userId'],
    ]);
});
test('should not create account with a registered provider', function () {
    $provider = Provider::factory()->create();

    $payload = [
        'provider_id' => $provider->provider_id,
        'username' => 'danielhe4rt',
    ];

    $response = $this
        ->actingAsAdmin()
        ->postJson(route('providers.store', ['provider' => $provider->provider]), $payload);

    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseMissing('users', [
        'username' => $payload['username'],
    ]);
});
