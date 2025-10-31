<?php

declare(strict_types=1);
use Heart\Meeting\Infrastructure\Models\Meeting;
use Illuminate\Support\Facades\Cache;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('end meeting', function () {
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
