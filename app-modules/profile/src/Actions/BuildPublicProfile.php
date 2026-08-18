<?php

declare(strict_types=1);

namespace He4rt\Profile\Actions;

use App\Models\Address;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\DTOs\PublicProfileData;
use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\WorkExperience;

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
        // A profile always exists in practice (UserObserver creates one on
        // signup), but it is a separate row, not a guaranteed relation — so the
        // page treats "no profile" the same as "empty profile".
        $profile = Profile::query()
            ->where('user_id', $user->getKey())
            ->first();

        $currentRole = $this->currentRole($profile);

        return new PublicProfileData(
            name: $user->name,
            username: $user->username,
            avatarUrl: $this->avatarUrl($user),
            coverUrl: $user->getFirstMediaUrl('cover') ?: null,
            nickname: $profile?->nickname,
            headline: $profile?->headline,
            currentPosition: $currentRole?->position,
            currentCompany: $currentRole?->company_name,
            availableForProposals: $profile instanceof Profile && $profile->available_for_proposals,
            location: $this->location($user),
        );
    }

    /**
     * The experience flagged as ongoing — what the header shows under the name.
     */
    private function currentRole(?Profile $profile): ?WorkExperience
    {
        return $profile?->workExperiences()
            ->where('is_currently_working_here', true)
            ->first();
    }

    private function avatarUrl(User $user): string
    {
        return $user->getFirstMediaUrl('avatar') ?: $user->getFilamentAvatarUrl();
    }

    /**
     * City, state and country joined into one line — never the zip code, which
     * narrows a public page down to someone's street.
     */
    private function location(User $user): ?string
    {
        /** @var Address|null $address */
        $address = $user->address()->first();

        if (!$address instanceof Address) {
            return null;
        }

        $parts = array_filter(
            [$address->city, $address->state, $address->country],
            filled(...),
        );

        return $parts === [] ? null : implode(', ', $parts);
    }
}
