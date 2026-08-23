<?php

declare(strict_types=1);

namespace He4rt\Identity\ExternalIdentity\Policies;

use He4rt\Identity\User\Models\User;

final class ExternalIdentityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function view(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function update(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function delete(User $user): bool
    {
        return $user->role->canManageUsers();
    }
}
