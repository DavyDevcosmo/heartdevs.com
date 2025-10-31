<?php

declare(strict_types=1);
use Heart\Character\Infrastructure\Models\Character;
use Symfony\Component\HttpFoundation\Response;
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

test('can fetch ranking', function () {
    Character::factory()->count(5)->create();

    $response = $this
        ->actingAsAdmin()
        ->getJson(route('ranking.leveling'));

    $response->assertStatus(Response::HTTP_OK);
});
