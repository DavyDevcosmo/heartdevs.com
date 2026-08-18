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
    public function __construct(
        public string $name,
        public string $username,
        public string $avatarUrl,
        public ?string $coverUrl = null,
        public ?string $nickname = null,
        public ?string $headline = null,
        public ?string $currentPosition = null,
        public ?string $currentCompany = null,
        public bool $availableForProposals = false,
        public ?string $location = null,
    ) {}
}
