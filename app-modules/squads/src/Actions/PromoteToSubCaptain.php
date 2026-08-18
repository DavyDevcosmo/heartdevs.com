<?php

declare(strict_types=1);

namespace He4rt\Squads\Actions;

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Enums\MembershipAction;
use He4rt\Squads\Enums\SquadRole;
use He4rt\Squads\Exceptions\NotAnActiveSquadMember;
use He4rt\Squads\Models\Squad;
use He4rt\Squads\Models\SquadMember;
use He4rt\Squads\Policies\SquadPolicy;
use Illuminate\Support\Facades\DB;

final readonly class PromoteToSubCaptain
{
    public function __construct(
        private SquadPolicy $squadPolicy,
        private RecordMembershipEvent $recordMembershipEvent,
    ) {}

    public function handle(User $actor, Squad $squad, User $subject, ?string $reason = null): SquadMember
    {
        $this->squadPolicy->authorize($actor, $squad);

        return $this->transition($squad, $subject, $actor, SquadRole::SubCaptain, $reason);
    }

    public function demote(User $actor, Squad $squad, User $subject, ?string $reason = null): SquadMember
    {
        $this->squadPolicy->authorize($actor, $squad);

        return $this->transition($squad, $subject, $actor, SquadRole::Member, $reason);
    }

    private function transition(
        Squad $squad,
        User $subject,
        User $actor,
        SquadRole $targetRole,
        ?string $reason,
    ): SquadMember {
        return DB::transaction(function () use ($squad, $subject, $actor, $targetRole, $reason): SquadMember {
            $member = SquadMember::query()
                ->where('squad_id', $squad->id)
                ->where('user_id', $subject->id)
                ->whereNot('role', SquadRole::ExMember)
                ->lockForUpdate()
                ->first();

            throw_if($member === null, NotAnActiveSquadMember::for($squad, $subject));

            $fromRole = $member->role;

            if ($fromRole === $targetRole) {
                return $member;
            }

            $member->update([
                'role' => $targetRole,
            ]);

            $this->recordMembershipEvent->handle(
                squad: $squad,
                subject: $subject,
                action: $this->actionFor($fromRole, $targetRole),
                fromRole: $fromRole,
                toRole: $targetRole,
                actor: $actor,
                reason: $reason,
            );

            return $member->refresh();
        });
    }

    private function actionFor(SquadRole $fromRole, SquadRole $targetRole): MembershipAction
    {
        return $fromRole === SquadRole::Member && $targetRole === SquadRole::SubCaptain
            ? MembershipAction::Promote
            : MembershipAction::Demote;
    }
}
