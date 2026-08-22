<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * The read-through store in front of every redirect.
 *
 * Two rules shape it:
 *
 * 1. **Raw columns only, never the verdict.** `expires_at` is stored as data and
 *    the status is evaluated per read, so expiry needs no scheduled invalidation.
 * 2. **Positive entries live forever, negative ones for a minute.** A hit is only
 *    ever wrong because someone edited the link — and the observer forgets the
 *    key on save. A miss, on the other hand, is what a slug scanner produces by
 *    the thousand; a short-lived sentinel keeps that traffic off Postgres without
 *    making a freshly created slug wait a minute to work.
 */
final class ShortLinkCache
{
    public const string KEY_PREFIX = 'shortlink:';

    public const int NEGATIVE_TTL_SECONDS = 60;

    /**
     * Sentinel for "this slug does not resolve" — distinguishable from an absent
     * key, which `Cache::get()` also reports as `null`.
     */
    private const string MISSING = '__missing__';

    public static function key(string $slug): string
    {
        return self::KEY_PREFIX.$slug;
    }

    /**
     * @param  Closure(): (array<string, mixed>|null)  $resolve
     * @return array<string, mixed>|null
     */
    public static function remember(string $slug, Closure $resolve): ?array
    {
        $cached = Cache::get(self::key($slug));

        if ($cached === self::MISSING) {
            return null;
        }

        if (is_array($cached)) {
            return $cached;
        }

        $payload = $resolve();

        if ($payload === null) {
            Cache::put(self::key($slug), self::MISSING, self::NEGATIVE_TTL_SECONDS);

            return null;
        }

        Cache::forever(self::key($slug), $payload);

        return $payload;
    }

    public static function forget(string $slug): void
    {
        Cache::forget(self::key($slug));
    }

    public static function has(string $slug): bool
    {
        return Cache::get(self::key($slug)) !== null;
    }
}
