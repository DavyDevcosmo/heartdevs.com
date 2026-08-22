<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Observers;

use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Support\ShortLinkCache;

/**
 * Keeps the redirect cache honest.
 *
 * The cache is written forever on purpose, so the *only* thing that can make a
 * stale entry is an edit — and this is where every edit is caught, whoever made
 * it (Action, Filament, tinker, seeder). Without it, "I changed the destination
 * and it works right now" would be false.
 */
final class ShortLinkObserver
{
    public function saved(ShortLink $link): void
    {
        $this->forget($link);
    }

    public function deleted(ShortLink $link): void
    {
        $this->forget($link);
    }

    public function restored(ShortLink $link): void
    {
        $this->forget($link);
    }

    private function forget(ShortLink $link): void
    {
        // The original value matters when the slug itself was rewritten in a
        // migration or fix-up: the stale key is the old one.
        $original = $link->getOriginal('slug');

        if (is_string($original) && $original !== '' && $original !== $link->slug) {
            ShortLinkCache::forget($original);
        }

        ShortLinkCache::forget($link->slug);
    }
}
