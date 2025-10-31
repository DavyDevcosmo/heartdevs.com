<?php

declare(strict_types=1);
use Heart\Season\Infrastructure\Models\Season;
use Illuminate\Support\Facades\Config;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('get current season success', function () {
    $season = Season::factory()->create();

    Config::set('he4rt.season.id', $season->id);

    $response = $this->actingAsAdmin()->get(route('seasons.current'));

    $response->assertOk();
    $response->assertJsonStructure([
        'id',
        'name',
        'description',
        'messagesCount',
        'participantsCount',
        'meetingCount',
        'badgesCount',
        'startAt',
        'endAt',
    ]);
});
