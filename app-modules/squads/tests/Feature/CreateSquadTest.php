<?php

declare(strict_types=1);

use He4rt\Identity\Tenant\Models\Tenant;
use He4rt\Identity\User\Models\User;
use He4rt\Squads\Actions\CreateSquad;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;

test('super-admin creates a squad as draft', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $tenant = Tenant::factory()->create([
        'name' => 'He4rt Developers',
    ]);

    $squad = resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $tenant,
        name: 'Test Squad',
        objective: 'This is a test squad.',
    );

    expect($squad)->toBeInstanceOf(Squad::class)
        ->and($squad->tenant_id)->toBe($tenant->id)
        ->and($squad->name)->toBe('Test Squad')
        ->and($squad->slug)->toBe('test-squad')
        ->and($squad->objective)->toBe('This is a test squad.')
        ->and($squad->status)->toBe(SquadStatus::Draft);

    $this->assertDatabaseHas('squads', [
        'tenant_id' => $tenant->id,
        'name' => 'Test Squad',
        'slug' => 'test-squad',
        'objective' => 'This is a test squad.',
        'status' => 'draft',
    ]);
});

test('duplicate squad names in same tenant receive unique slugs', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $tenant = Tenant::factory()->create();

    $firstSquad = resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $tenant,
        name: 'Test Squad',
    );
    $secondSquad = resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $tenant,
        name: 'Test Squad',
    );

    expect($firstSquad->slug)->toBe('test-squad')
        ->and($secondSquad->slug)->toBe('test-squad-2');
});

test('same squad name can reuse slug in different tenants', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $firstTenant = Tenant::factory()->create();
    $secondTenant = Tenant::factory()->create();

    $firstSquad = resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $firstTenant,
        name: 'Test Squad',
    );
    $secondSquad = resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $secondTenant,
        name: 'Test Squad',
    );

    expect($firstSquad->slug)->toBe('test-squad')
        ->and($secondSquad->slug)->toBe('test-squad');
});

test('common user cannot create a squad', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'common-user',
    ]);
    $tenant = Tenant::factory()->create();

    resolve(CreateSquad::class)->handle(
        actor: $actor,
        tenant: $tenant,
        name: 'Test Squad',
    );
})->throws(AuthorizationException::class);
