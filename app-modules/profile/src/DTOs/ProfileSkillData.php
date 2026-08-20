<?php

declare(strict_types=1);

namespace He4rt\Profile\DTOs;

/**
 * One skill as it appears on the public profile: already labelled, already
 * translated, nothing left for the view to resolve.
 */
final readonly class ProfileSkillData
{
    public function __construct(
        public string $name,
        public string $category,
        public string $proficiency,
        public ?int $yearsExperience = null,
    ) {}
}
