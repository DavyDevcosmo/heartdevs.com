<?php

declare(strict_types=1);

use He4rt\Feedback\Models\Feedback;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

uses(DatabaseTransactions::class);

test('can find by id', function (): void {
    $feedback = Feedback::factory()->create();

    $this
        ->actingAsAdmin()
        ->getJson(route('feedbacks.show', ['feedbackId' => $feedback->id]))
        ->assertStatus(Response::HTTP_OK);
});
