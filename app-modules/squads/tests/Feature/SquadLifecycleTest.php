<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Actions\ManageSquadStatus;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Exceptions\InvalidSquadStatusTransition;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;

describe('ManageSquadStatus', static function (): void {
    test('super-admin moves a squad through a valid transition', function (SquadStatus $from, SquadStatus $to): void {
        config(['he4rt.admins' => 'guisaliba']);

        $actor = User::factory()->create([
            'username' => 'guisaliba',
        ]);
        $squad = Squad::factory()->create([
            'status' => $from,
        ]);

        $updatedSquad = resolve(ManageSquadStatus::class)->handle(
            actor: $actor,
            squad: $squad,
            status: $to,
        );

        expect($updatedSquad->status)->toBe($to)
            ->and($squad->fresh()->status)->toBe($to);
    })->with('valid transitions');

    test('a squad cannot move through a transition outside the lifecycle', function (SquadStatus $from, SquadStatus $to): void {
        config(['he4rt.admins' => 'guisaliba']);

        $actor = User::factory()->create([
            'username' => 'guisaliba',
        ]);
        $squad = Squad::factory()->create([
            'status' => $from,
        ]);

        resolve(ManageSquadStatus::class)->handle(
            actor: $actor,
            squad: $squad,
            status: $to,
        );
    })->with('invalid transitions')->throws(InvalidSquadStatusTransition::class);

    test('a common user cannot change a squad status', function (): void {
        config(['he4rt.admins' => 'guisaliba']);

        $actor = User::factory()->create([
            'username' => 'common-user',
        ]);
        $squad = Squad::factory()->create([
            'status' => SquadStatus::Draft,
        ]);

        resolve(ManageSquadStatus::class)->handle(
            actor: $actor,
            squad: $squad,
            status: SquadStatus::Active,
        );
    })->throws(AuthorizationException::class);
});

dataset('valid transitions', [
    'draft → active' => [SquadStatus::Draft, SquadStatus::Active],
    'active → inactive' => [SquadStatus::Active, SquadStatus::Inactive],
    'inactive → active (reactivate)' => [SquadStatus::Inactive, SquadStatus::Active],
    'inactive → archived' => [SquadStatus::Inactive, SquadStatus::Archived],
]);

dataset('invalid transitions', [
    'draft → inactive' => [SquadStatus::Draft, SquadStatus::Inactive],
    'draft → archived' => [SquadStatus::Draft, SquadStatus::Archived],
    'active → draft' => [SquadStatus::Active, SquadStatus::Draft],
    'active → archived' => [SquadStatus::Active, SquadStatus::Archived],
    'archived → active' => [SquadStatus::Archived, SquadStatus::Active],
    'archived → inactive' => [SquadStatus::Archived, SquadStatus::Inactive],
    'same status (active → active)' => [SquadStatus::Active, SquadStatus::Active],
]);
