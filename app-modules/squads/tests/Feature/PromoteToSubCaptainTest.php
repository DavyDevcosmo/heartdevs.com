<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Actions\PromoteToSubCaptain;
use He4rt\Squads\Enums\MembershipAction;
use He4rt\Squads\Enums\SquadRole;
use He4rt\Squads\Exceptions\NotAnActiveSquadMember;
use He4rt\Squads\Models\Squad;
use He4rt\Squads\Models\SquadMember;
use He4rt\Squads\Models\SquadMembershipEvent;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function (): void {
    config(['he4rt.admins' => 'guisaliba']);

    $this->admin = User::factory()->create([
        'username' => 'guisaliba',
    ]);
});

test('a captain promotes a member to sub-captain and records the transition', function (): void {
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

    $member = resolve(PromoteToSubCaptain::class)->handle(
        actor: $captain,
        squad: $squad,
        subject: $subject,
        reason: 'Elected off-system.',
    );

    expect($member->role)->toBe(SquadRole::SubCaptain);

    $this->assertDatabaseHas('squad_membership_events', [
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'actor_id' => $captain->id,
        'action' => MembershipAction::Promote->value,
        'from_role' => SquadRole::Member->value,
        'to_role' => SquadRole::SubCaptain->value,
        'reason' => 'Elected off-system.',
    ]);
});

test('a captain demotes a sub-captain to member and records the transition', function (): void {
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
        'role' => SquadRole::SubCaptain,
    ]);

    $member = resolve(PromoteToSubCaptain::class)->demote(
        actor: $captain,
        squad: $squad,
        subject: $subject,
    );

    expect($member->role)->toBe(SquadRole::Member);

    $this->assertDatabaseHas('squad_membership_events', [
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'actor_id' => $captain->id,
        'action' => MembershipAction::Demote->value,
        'from_role' => SquadRole::SubCaptain->value,
        'to_role' => SquadRole::Member->value,
    ]);
});

test('a captain can become a sub-captain and vacate the captain seat', function (): void {
    $squad = Squad::factory()->create();
    $captain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);

    $member = resolve(PromoteToSubCaptain::class)->handle(
        actor: $captain,
        squad: $squad,
        subject: $captain,
    );

    expect($member->role)->toBe(SquadRole::SubCaptain)
        ->and($squad->captain()->first())->toBeNull();

    $this->assertDatabaseHas('squad_membership_events', [
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'actor_id' => $captain->id,
        'action' => MembershipAction::Demote->value,
        'from_role' => SquadRole::Captain->value,
        'to_role' => SquadRole::SubCaptain->value,
    ]);
});

test('a super-admin can demote a captain to member', function (): void {
    $squad = Squad::factory()->create();
    $captain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);

    $member = resolve(PromoteToSubCaptain::class)->demote(
        actor: $this->admin,
        squad: $squad,
        subject: $captain,
    );

    expect($member->role)->toBe(SquadRole::Member)
        ->and($squad->captain()->first())->toBeNull();

    $this->assertDatabaseHas('squad_membership_events', [
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'actor_id' => $this->admin->id,
        'action' => MembershipAction::Demote->value,
        'from_role' => SquadRole::Captain->value,
        'to_role' => SquadRole::Member->value,
    ]);
});

test('a sub-captain can promote a member in their own squad', function (): void {
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

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $subCaptain,
        squad: $squad,
        subject: $subject,
    );

    $this->assertDatabaseHas('squad_members', [
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::SubCaptain->value,
    ]);
});

test('a common member cannot change a squad role', function (): void {
    $squad = Squad::factory()->create();
    $actor = User::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $actor->id,
        'role' => SquadRole::Member,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $actor,
        squad: $squad,
        subject: $subject,
    );
})->throws(AuthorizationException::class);

test('a leader cannot change a different squad', function (): void {
    $ownSquad = Squad::factory()->create();
    $otherSquad = Squad::factory()->create();
    $captain = User::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $ownSquad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $otherSquad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::Member,
    ]);

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $captain,
        squad: $otherSquad,
        subject: $subject,
    );
})->throws(AuthorizationException::class);

test('a missing active membership cannot be changed', function (): void {
    $squad = Squad::factory()->create();
    $subject = User::factory()->create();

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $subject,
    );
})->throws(NotAnActiveSquadMember::class);

test('an ex-member cannot be changed', function (): void {
    $squad = Squad::factory()->create();
    $subject = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subject->id,
        'role' => SquadRole::ExMember,
    ]);

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $this->admin,
        squad: $squad,
        subject: $subject,
    );
})->throws(NotAnActiveSquadMember::class);

test('same-state role changes create no event', function (): void {
    $squad = Squad::factory()->create();
    $captain = User::factory()->create();
    $subCaptain = User::factory()->create();
    $member = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::SubCaptain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $member->id,
        'role' => SquadRole::Member,
    ]);

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $captain,
        squad: $squad,
        subject: $subCaptain,
    );
    resolve(PromoteToSubCaptain::class)->demote(
        actor: $captain,
        squad: $squad,
        subject: $member,
    );

    expect(SquadMembershipEvent::query()->where('squad_id', $squad->id)->count())->toBe(0);
});

test('a sub-captain cannot demote the squad captain', function (): void {
    $squad = Squad::factory()->create();
    $subCaptain = User::factory()->create();
    $captain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::SubCaptain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);

    resolve(PromoteToSubCaptain::class)->demote(
        actor: $subCaptain,
        squad: $squad,
        subject: $captain,
    );
})->throws(AuthorizationException::class);

test('a sub-captain cannot move the captain down to sub-captain', function (): void {
    $squad = Squad::factory()->create();
    $subCaptain = User::factory()->create();
    $captain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::SubCaptain,
    ]);
    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $captain->id,
        'role' => SquadRole::Captain,
    ]);

    resolve(PromoteToSubCaptain::class)->handle(
        actor: $subCaptain,
        squad: $squad,
        subject: $captain,
    );
})->throws(AuthorizationException::class);

test('a sub-captain can demote themselves', function (): void {
    $squad = Squad::factory()->create();
    $subCaptain = User::factory()->create();

    SquadMember::factory()->create([
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::SubCaptain,
    ]);

    resolve(PromoteToSubCaptain::class)->demote(
        actor: $subCaptain,
        squad: $squad,
        subject: $subCaptain,
    );

    $this->assertDatabaseHas('squad_members', [
        'squad_id' => $squad->id,
        'user_id' => $subCaptain->id,
        'role' => SquadRole::Member->value,
    ]);
});
