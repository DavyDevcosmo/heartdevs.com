<?php

declare(strict_types=1);

namespace He4rt\Profile\DTOs;

/**
 * One badge earned by the member, flattened to what the page shows.
 *
 * The Badge model also carries `redeem_code` — the string anyone can type to
 * claim that badge for themselves. It is deliberately absent here: publishing it
 * on an unauthenticated page would hand the badge to every visitor.
 */
final readonly class ProfileBadgeData
{
    public function __construct(
        public string $name,
        public string $description,
        public ?string $imageUrl = null,
    ) {}
}
