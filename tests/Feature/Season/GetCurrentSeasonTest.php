<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Heart\Season\Infrastructure\Models\Season;
use Illuminate\Support\Facades\Config;

uses(DatabaseTransactions::class);

test('get current season success', function (): void {
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
