<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;

beforeEach(function (): void {
    $this->withoutVite();
});

it('renders a public profile without authentication', function (): void {
    User::factory()->create([
        'name' => 'Daniel Reis',
        'username' => 'danielhe4rt',
    ]);

    $this->get('/@danielhe4rt')
        ->assertOk()
        ->assertSee('Daniel Reis')
        ->assertSee('@danielhe4rt');
    $this->assertGuest();
});

it('returns 404 for an unknown username', function (): void {
    $this->get('/@ninguem')->assertNotFound();
});

it('returns 404 for a banned user', function (): void {
    User::factory()->create([
        'username' => 'banido',
        'banned_at' => now(),
    ]);

    $this->get('/@banido')->assertNotFound();
});

it('still renders the profile of a suspended user', function (): void {
    User::factory()->create([
        'name' => 'Suspenso Temporariamente',
        'username' => 'suspenso',
        'suspended_until' => now()->addDays(7),
    ]);

    $this->get('/@suspenso')
        ->assertOk()
        ->assertSee('Suspenso Temporariamente');
});
