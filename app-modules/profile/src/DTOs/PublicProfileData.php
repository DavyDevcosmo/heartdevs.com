<?php

declare(strict_types=1);

namespace He4rt\Profile\DTOs;

/**
 * Everything the public profile page is allowed to render.
 *
 * The page is public and unauthenticated, while the models behind it carry
 * fields that must never leak (OAuth credentials, expected salary, badge
 * redeem codes, birthdate). The view never receives a model: this DTO is the
 * allowlist, and BuildPublicProfile is the only place that fills it.
 */
final readonly class PublicProfileData
{
    /**
     * @param  list<string>  $employmentTypes  Already translated labels, not enum values.
     * @param  list<ProfileLinkData>  $socialLinks
     * @param  list<ProfileLinkData>  $connectedAccounts
     * @param  list<ProfileSkillData>  $skills
     * @param  list<WorkExperienceData>  $experiences
     * @param  list<ProfileBadgeData>  $badges
     */
    public function __construct(
        public string $name,
        public string $username,
        public ?string $avatarUrl,
        public ?string $coverUrl = null,
        public ?string $nickname = null,
        public ?string $headline = null,
        public ?string $currentPosition = null,
        public ?string $currentCompany = null,
        public bool $availableForProposals = false,
        public ?string $location = null,
        public ?string $about = null,
        public ?string $seniority = null,
        public ?int $yearsExperience = null,
        public ?string $startAvailability = null,
        public bool $openToRemote = false,
        public bool $willingToRelocate = false,
        public array $employmentTypes = [],
        public array $socialLinks = [],
        public array $connectedAccounts = [],
        public array $skills = [],
        public array $experiences = [],
        public ?int $level = null,
        // Total XP and how far into the current level it sits, for the progress
        // bar. Derived from the Character; there is no private counterpart.
        public ?int $experience = null,
        public ?float $levelProgress = null,
        // XP que falta pro próximo nível; null no teto, onde não há próximo.
        public ?int $experienceToNextLevel = null,
        // "1 ano e 4 meses" — há quanto tempo a pessoa entrou na comunidade.
        public ?string $memberFor = null,
        public array $badges = [],
    ) {}
}
