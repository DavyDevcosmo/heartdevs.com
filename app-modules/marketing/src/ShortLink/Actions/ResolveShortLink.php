<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Actions;

use He4rt\Marketing\ShortLink\DTOs\Resolution;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Support\ShortLinkCache;

/**
 * The hottest path in the module: every click passes through here.
 *
 * It answers with data, never with an HTTP response — deciding to redirect or to
 * render the "unavailable" page belongs to `portal`. Soft-deleted links are
 * invisible to the query, so they collapse into the same "does not resolve"
 * answer as an unknown slug: the edge cannot become a slug-enumeration oracle.
 */
final readonly class ResolveShortLink
{
    public function execute(string $slug): Resolution
    {
        $payload = ShortLinkCache::remember($slug, fn (): ?array => $this->lookup($slug));

        if ($payload === null) {
            return Resolution::missing();
        }

        return Resolution::fromPayload($payload);
    }

    /**
     * Raw columns only — the status is a function of these, evaluated per read.
     *
     * @return array<string, mixed>|null
     */
    private function lookup(string $slug): ?array
    {
        /** @var ShortLink|null $link */
        $link = ShortLink::query()
            ->select(['id', 'slug', 'destination_url', 'utm', 'active', 'expires_at'])
            ->where('slug', $slug)
            ->first();

        if ($link === null) {
            return null;
        }

        return [
            'id' => (string) $link->getKey(),
            'destination_url' => $link->destination_url,
            'utm' => $link->utm->toArray(),
            'active' => (bool) $link->active,
            'expires_at' => $link->expires_at?->toIso8601String(),
        ];
    }
}
