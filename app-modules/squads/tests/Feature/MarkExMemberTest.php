<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Actions\MarkExMember;
use He4rt\Squads\Enums\MembershipAction;
use He4rt\Squads\Enums\SquadRole;
use He4rt\Squads\Exceptions\NotAnActiveSquadMember;
use He4rt\Squads\Models\Squad;
use He4rt\Squads\Models\SquadMember;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $this->admin = User::factory()->create([
        'username' => 'guisaliba',
    ]);
});

test('captain marks an active member as ex-member and the trail records it', function (): void {
    $squad = Squad::factory()->create();
    $captain = User::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    $member = resolve(MarkExMember::class)->handle(
        actor: $captain,
        squad: $squad,
        subject: $subject,
        reason: 'Inatividade prolongada.',
    );

    expect($member->role)->toBe(SquadRole::ExMember)
        ->and($member->left_at)->not->toBeNull();

    $this->assertDatabaseHas('squad_members', [
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::ExMember->value,
    ]);

    $this->assertDatabaseHas('squad_membership_events', [
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'actor_id' => $captain->id,
        'action' => MembershipAction::Leave->value,
        'from_role' => SquadRole::Member->value,
        'to_role' => SquadRole::ExMember->value,
        'reason' => 'Inatividade prolongada.',
    ]);
});

test('super-admin marks ex-member on a squad they do not belong to', function (): void {
    $squad = Squad::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    $member = resolve(MarkExMember::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $subject,
    );

    expect($member->role)->toBe(SquadRole::ExMember);
});

test('sub-captain can mark ex-member in their own squad', function (): void {
    $squad = Squad::factory()->create();
    $subCaptain = User::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::SubCaptain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    $member = resolve(MarkExMember::class)->handle(
        actor: $subCaptain,
        squad: $squad,
        subject: $subject,
    );

    expect($member->role)->toBe(SquadRole::ExMember);
});

test('marking the captain as ex-member leaves the squad vacant', function (): void {
    $squad = Squad::factory()->create();
    $captain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);

    expect($squad->captain()->first())->not->toBeNull();

    resolve(MarkExMember::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $captain,
    );

    expect($squad->captain()->first())->toBeNull();
});

test('a common member cannot mark anyone ex-member', function (): void {
    $squad = Squad::factory()->create();
    $member = User::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $member->id,
        'role' => SquadRole::Member,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    resolve(MarkExMember::class)->handle(
        actor: $member,
        squad: $squad,
        subject: $subject,
    );
})->throws(AuthorizationException::class);

test('marking a subject with no active membership throws', function (): void {
    $squad = Squad::factory()->create();
    $outsider = User::factory()->create();

    resolve(MarkExMember::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $outsider,
    );
})->throws(NotAnActiveSquadMember::class);

test('marking an already ex-member subject throws', function (): void {
    $squad = Squad::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::ExMember,
    ]);

    resolve(MarkExMember::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $subject,
    );
})->throws(NotAnActiveSquadMember::class);
