<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Actions\ActivateSquad;
use He4rt\Squads\Actions\ArchiveSquad;
use He4rt\Squads\Actions\DeactivateSquad;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;

test('super-admin activates a draft squad', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Draft,
    ]);

    $activatedSquad = resolve(ActivateSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );

    expect($activatedSquad->status)->toBe(SquadStatus::Active)
        ->and($squad->fresh()->status)->toBe(SquadStatus::Active);
});

test('common user cannot activate a squad', function (): void {
    config(['he4rt.admins' => 'danielhe4rt']);

    $actor = User::factory()->create([
        'username' => 'common-user',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Draft,
    ]);

    resolve(ActivateSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );
})->throws(AuthorizationException::class);

test('super-admin deactivates an active squad', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Active,
    ]);

    $deactivatedSquad = resolve(DeactivateSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );

    expect($deactivatedSquad->status)->toBe(SquadStatus::Inactive)
        ->and($squad->fresh()->status)->toBe(SquadStatus::Inactive);
});

test('common user cannot deactivate a squad', function (): void {
    config(['he4rt.admins' => 'danielhe4rt']);

    $actor = User::factory()->create([
        'username' => 'common-user',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Active,
    ]);

    resolve(DeactivateSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );
})->throws(AuthorizationException::class);

test('super-admin archives an inactive squad', function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $actor = User::factory()->create([
        'username' => 'guisaliba',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Inactive,
    ]);

    $archivedSquad = resolve(ArchiveSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );

    expect($archivedSquad->status)->toBe(SquadStatus::Archived)
        ->and($squad->fresh()->status)->toBe(SquadStatus::Archived);
});

test('common user cannot archive a squad', function (): void {
    config(['he4rt.admins' => 'danielhe4rt']);

    $actor = User::factory()->create([
        'username' => 'common-user',
    ]);
    $squad = Squad::factory()->create([
        'status' => SquadStatus::Inactive,
    ]);

    resolve(ArchiveSquad::class)->handle(
        actor: $actor,
        squad: $squad,
    );
})->throws(AuthorizationException::class);
