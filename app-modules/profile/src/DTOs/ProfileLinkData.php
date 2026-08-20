<?php

declare(strict_types=1);

namespace He4rt\Profile\DTOs;

/**
 * One rendered link on the public profile — a social platform or a connected
 * OAuth account, flattened to what the view needs.
 *
 * A null $url means "show the handle, do not link it": Discord accounts have no
 * public profile page to point at.
 */
final readonly class ProfileLinkData
{
    public function __construct(
        public string $label,
        public string $handle,
        public string $icon,
        public ?string $url = null,
    ) {}
}
