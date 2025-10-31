<?php

declare(strict_types=1);
use Heart\Provider\Infrastructure\Models\Provider;
use Symfony\Component\HttpFoundation\Response;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('can create', function () {
    $providerSender = Provider::factory()->create(['provider' => 'discord']);
    $providerTarget = Provider::factory()->create(['provider' => 'discord']);

    $payload = [
        'sender_id' => $providerSender->provider_id,
        'target_id' => $providerTarget->provider_id,
        'message' => 'mt legal vc',
        'type' => 'elogio',
    ];

    $this
        ->actingAsAdmin()
        ->postJson(route('feedbacks.create'), $payload)
        ->assertStatus(Response::HTTP_CREATED);

    $payload['sender_id'] = $providerSender->user->id;
    $payload['target_id'] = $providerTarget->user->id;
    $this->assertDatabaseHas('feedbacks', $payload);
});
