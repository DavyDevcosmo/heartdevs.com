<?php

declare(strict_types=1);

use He4rt\Feedback\Models\Feedback;
use He4rt\Provider\Models\Provider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

uses(DatabaseTransactions::class);

dataset('dataProvider', fn () => [
    'approve feedback' => [
        'action' => 'approved',
        'payload' => [],
        'expected' => [
            'status' => 'approved',
        ],
    ],
    'decline feedback' => [
        'action' => 'declined',
        'payload' => [
            'reason' => 'bobo',
        ],
        'expected' => [
            'status' => 'declined',
            'reason' => 'bobo',
        ],
    ],
]);
test('can handle feedback', function (string $action, array $payload, array $expected): void {
    $feedback = Feedback::factory()->create();
    $staffProvider = Provider::factory()->create(['provider' => 'discord']);

    $payload['staff_id'] = $staffProvider->provider_id;
    $response = $this
        ->actingAsAdmin()
        ->postJson(route('feedbacks.review', [
            'feedbackId' => $feedback->id,
            'action' => $action,
        ]), $payload);

    $response->assertStatus(Response::HTTP_CREATED);

    $expected['staff_id'] = $staffProvider->user_id;
    $this->assertDatabaseHas('feedback_reviews', $expected);
})->with('dataProvider');
