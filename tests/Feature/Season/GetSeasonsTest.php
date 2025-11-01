<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Heart\Season\Infrastructure\Models\Season;

uses(DatabaseTransactions::class);

test('get seasons success', function (): void {
    Season::factory()->create();

    $response = $this->actingAsAdmin()->get(route('get-seasons'));

    $response->assertOk();
    $response->assertJsonStructure([
        [
            'id',
            'name',
            'description',
            'messagesCount',
            'participantsCount',
            'meetingCount',
            'badgesCount',
            'startAt',
            'endAt',
        ],
    ]);
});
