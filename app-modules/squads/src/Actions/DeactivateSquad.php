<?php

declare(strict_types=1);

namespace He4rt\Squads\Actions;

use He4rt\Identity\User\Models\User;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;

final class DeactivateSquad
{
    public function handle(User $actor, Squad $squad): Squad
    {
        if (!$actor->isAdmin()) {
            throw new AuthorizationException();
        }

        $squad->update([
            'status' => SquadStatus::Inactive,
        ]);

        return $squad->refresh();
    }
}
