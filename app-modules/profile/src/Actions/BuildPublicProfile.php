<?php

declare(strict_types=1);

namespace He4rt\Profile\Actions;

use App\Models\Address;
use Carbon\CarbonInterface;
use He4rt\Gamification\Badge\Models\Badge;
use He4rt\Gamification\Character\Models\Character;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use He4rt\Identity\ExternalIdentity\Models\ExternalIdentity;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Data\WorkPreferences;
use He4rt\Profile\DTOs\ProfileBadgeData;
use He4rt\Profile\DTOs\ProfileLinkData;
use He4rt\Profile\DTOs\ProfileSkillData;
use He4rt\Profile\DTOs\PublicProfileData;
use He4rt\Profile\DTOs\WorkExperienceData;
use He4rt\Profile\Enums\EmploymentType;
use He4rt\Profile\Enums\SocialPlatform;
use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\ProfileSkill;
use He4rt\Profile\Models\WorkExperience;
use Illuminate\Database\Eloquent\Collection;

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

        // Fetched once and used twice: the header needs the ongoing job, the
        // resume section needs the whole list. The relation already sorts
        // ongoing first, then most recent.
        $experiences = $profile instanceof Profile
            ? $profile->workExperiences()->get()
            : new Collection();

        $currentRole = $experiences->first(
            static fn (WorkExperience $experience): bool => $experience->is_currently_working_here,
        );
        $preferences = $profile?->preferences;

        // Gamification lives in its own module and its own row: a member who
        // never touched the bot has no Character at all, and the whole section
        // disappears rather than showing a level 1 they never played for.
        //
        // badges.media, not badges: every badge reaches for its image, and
        // without the media loaded up front that is one query per badge.
        $character = Character::query()
            ->with('badges.media')
            ->where('user_id', $user->getKey())
            ->first();

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
            about: $profile?->about,
            seniority: $profile?->seniority_level?->getLabel(),
            yearsExperience: $profile?->years_experience,
            startAvailability: $profile?->start_availability?->getLabel(),
            openToRemote: $preferences instanceof WorkPreferences && $preferences->isOpenToRemote,
            willingToRelocate: $preferences instanceof WorkPreferences && $preferences->willingToRelocate,
            employmentTypes: $this->employmentTypes($preferences),
            socialLinks: $this->socialLinks($profile),
            connectedAccounts: $this->connectedAccounts($user),
            skills: $this->skills($profile),
            experiences: $this->experiences($experiences),
            level: $character?->level,
            experience: $character?->experience,
            levelProgress: $character?->percentage_experience,
            experienceToNextLevel: $character instanceof Character && $character->experience_progress > 0
                ? $character->experience_progress
                : null,
            memberFor: $this->humanDuration(
                $user->created_at instanceof CarbonInterface
                    ? (int) $user->created_at->diffInMonths(now())
                    : null,
            ),
            badges: $this->badges($character),
        );
    }

    /**
     * Earned badges, without the redeem code that would let any visitor claim
     * the same badge.
     *
     * @return list<ProfileBadgeData>
     */
    private function badges(?Character $character): array
    {
        if (!$character instanceof Character) {
            return [];
        }

        $badges = [];

        $rows = $character->badges
            ->sortBy(static fn (Badge $badge): string => $badge->name)
            ->values();

        foreach ($rows as $badge) {
            $badges[] = new ProfileBadgeData(
                name: $badge->name,
                description: $badge->description,
                imageUrl: $badge->getFirstMediaUrl('badge') ?: null,
            );
        }

        return $badges;
    }

    /**
     * @return list<ProfileSkillData>
     */
    private function skills(?Profile $profile): array
    {
        if (!$profile instanceof Profile) {
            return [];
        }

        $skills = [];

        // profileSkills() instead of skills(): the pivot is untyped, while the
        // ProfileSkill model casts proficiency to its enum.
        $rows = $profile->profileSkills()
            ->with('skill')
            ->get()
            ->sortBy(static fn (ProfileSkill $row): string => $row->skill->name)
            ->values();

        foreach ($rows as $row) {
            $skills[] = new ProfileSkillData(
                name: $row->skill->name,
                category: $row->skill->category->getLabel(),
                proficiency: $row->proficiency->getLabel(),
                yearsExperience: $row->years_experience,
            );
        }

        return $skills;
    }

    /**
     * @param  Collection<int, WorkExperience>  $rows
     * @return list<WorkExperienceData>
     */
    private function experiences(Collection $rows): array
    {
        $experiences = [];

        foreach ($rows as $experience) {
            $experiences[] = new WorkExperienceData(
                company: $experience->company_name,
                position: $experience->position,
                period: $this->period($experience),
                description: $experience->description,
                duration: $this->humanDuration($experience->durationInMonths()),
                isCurrent: $experience->is_currently_working_here,
            );
        }

        return $experiences;
    }

    private function period(WorkExperience $experience): string
    {
        $start = $experience->start_date->format('m/Y');

        if ($experience->is_currently_working_here) {
            return $start.' — atual';
        }

        // A past job with no end date: show the start alone rather than implying
        // it ran until today.
        return $experience->end_date instanceof CarbonInterface
            ? $start.' — '.$experience->end_date->format('m/Y')
            : $start;
    }

    private function humanDuration(?int $months): ?string
    {
        if ($months === null || $months < 1) {
            return null;
        }

        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;
        $parts = [];

        if ($years > 0) {
            $parts[] = $years.($years === 1 ? ' ano' : ' anos');
        }

        if ($remainingMonths > 0) {
            $parts[] = $remainingMonths.($remainingMonths === 1 ? ' mês' : ' meses');
        }

        return implode(' e ', $parts);
    }

    /**
     * @return list<ProfileLinkData>
     */
    private function socialLinks(?Profile $profile): array
    {
        if (!$profile instanceof Profile) {
            return [];
        }

        $links = [];

        foreach ($profile->social_links ?? [] as $key => $handle) {
            $platform = SocialPlatform::tryFrom((string) $key);
            if (!$platform instanceof SocialPlatform) {
                continue;
            }

            if (blank($handle)) {
                continue;
            }

            $links[] = new ProfileLinkData(
                label: $platform->getLabel(),
                handle: $handle,
                icon: $platform->getBrandIcon(),
                url: $platform->getUrl($handle),
            );
        }

        return $links;
    }

    /**
     * Connected OAuth accounts, limited to the providers the platform supports.
     *
     * Only the handle is read out of the identity: `metadata['email']` and the
     * `credentials` cast (OAuth tokens) stay behind.
     *
     * @return list<ProfileLinkData>
     */
    private function connectedAccounts(User $user): array
    {
        $supported = IdentityProvider::supportedProviders();
        $accounts = [];

        /** @var ExternalIdentity $identity */
        foreach ($user->providers()->get() as $identity) {
            if (!$identity->isConnected()) {
                continue;
            }

            if (!in_array($identity->provider, $supported, strict: true)) {
                continue;
            }

            $handle = $identity->metadata['username'] ?? null;
            if (!is_string($handle)) {
                continue;
            }

            if (blank($handle)) {
                continue;
            }

            $accounts[] = new ProfileLinkData(
                label: $identity->provider->getLabel(),
                handle: $handle,
                icon: $identity->provider->getIcon(),
                url: $identity->provider->profileUrl($handle),
            );
        }

        return $accounts;
    }

    /**
     * @return list<string>
     */
    private function employmentTypes(?WorkPreferences $preferences): array
    {
        if (!$preferences instanceof WorkPreferences) {
            return [];
        }

        return array_map(
            static fn (EmploymentType $type): string => $type->getLabel(),
            $preferences->employmentTypes,
        );
    }

    /**
     * The uploaded avatar, else the picture of the GitHub account the member
     * actually connected.
     *
     * Deliberately not `getFilamentAvatarUrl()`: that one builds
     * github.com/{username}.png out of the He4rt username, which assumes the two
     * handles are the same person. When they are not, the page either shows a
     * broken image or — worse — a stranger's face, because someone else owns
     * that GitHub account. Null here means "no picture", and the view draws
     * initials instead of guessing.
     */
    private function avatarUrl(User $user): ?string
    {
        $uploaded = $user->getFirstMediaUrl('avatar');

        if ($uploaded !== '') {
            return $uploaded;
        }

        $handle = $this->githubHandle($user);

        return $handle === null ? null : sprintf('https://github.com/%s.png', $handle);
    }

    private function githubHandle(User $user): ?string
    {
        /** @var ExternalIdentity|null $identity */
        $identity = $user->providers()
            ->where('provider', IdentityProvider::GitHub)
            ->first();

        if (!$identity instanceof ExternalIdentity || !$identity->isConnected()) {
            return null;
        }

        $handle = $identity->metadata['username'] ?? null;

        return is_string($handle) && filled($handle) ? $handle : null;
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
