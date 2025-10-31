<?php

declare(strict_types=1);
use Heart\Feedback\Infrastructure\Models\Feedback;
use Heart\Provider\Infrastructure\Models\Provider;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

dataset('dataProvider', function () {
    return [
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
    ];
});
test('can handle feedback', function (string $action, array $payload, array $expected) {
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
