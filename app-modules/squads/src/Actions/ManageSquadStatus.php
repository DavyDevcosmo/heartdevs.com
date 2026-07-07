<?php

declare(strict_types=1);

namespace He4rt\Squads\Actions;

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Exceptions\InvalidSquadStatusTransition;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;

final class ManageSquadStatus
{
    public function handle(User $actor, Squad $squad, SquadStatus $status): Squad
    {
        throw_unless($actor->isAdmin(), AuthorizationException::class);

        if (!$squad->status->canTransitionTo($status)) {
            throw InvalidSquadStatusTransition::between($squad->status, $status);
        }

        $squad->update([
            'status' => $status,
        ]);

        return $squad->refresh();
    }
}
