<?php

declare(strict_types=1);
use Heart\Season\Infrastructure\Models\Season;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('get seasons success', function () {
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
