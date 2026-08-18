<?php

declare(strict_types=1);

namespace He4rt\Profile\Actions;

use He4rt\Identity\User\Models\User;
use He4rt\Profile\DTOs\PublicProfileData;

/**
 * Maps a User onto the fields the public profile page may render.
 *
 * Every new section on the page starts here: if a field is not copied into
 * PublicProfileData, it cannot reach the view.
 */
final class BuildPublicProfile
{
    public function handle(User $user): PublicProfileData
    {
        return new PublicProfileData(
            name: $user->name,
            username: $user->username,
        );
    }
}
