<?php

declare(strict_types=1);
use Heart\Feedback\Infrastructure\Models\Feedback;
use Symfony\Component\HttpFoundation\Response;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('can find by id', function () {
    $feedback = Feedback::factory()->create();

    $this
        ->actingAsAdmin()
        ->getJson(route('feedbacks.show', ['feedbackId' => $feedback->id]))
        ->assertStatus(Response::HTTP_OK);
});
