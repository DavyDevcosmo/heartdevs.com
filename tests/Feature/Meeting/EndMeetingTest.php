<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use src\Models\Meeting;

uses(DatabaseTransactions::class);

test('end meeting', function (): void {
    $meeting = Meeting::factory()->create();
    Cache::tags(['meetings'])->set('current-meeting', $meeting->id);

    $this->actingAsAdmin()
        ->postJson(route('events.meeting.postEndMeeting', ['provider' => 'discord']))
        ->assertNoContent();

    $this->assertDatabaseMissing('meetings', [
        'id' => $meeting->id,
        'ends_at' => 'null',
    ]);
});
