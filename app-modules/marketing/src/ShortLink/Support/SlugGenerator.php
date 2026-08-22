<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Support;

use Illuminate\Support\Str;

/**
 * Builds `{apelido}-{5 chars base36}` slugs.
 *
 * The apelido half is what makes the link pasteable (`/l/discord-a3f9k` instead
 * of a query-string wall); the generated half is what makes it unique without a
 * collision-retry loop and without leaking how many links exist.
 *
 * `random_int` over an explicit alphabet — not `Str::random()`, which draws from
 * `[A-Za-z0-9]` and would produce uppercase characters the `[a-z0-9-]+` route
 * constraint rejects.
 */
final class SlugGenerator
{
    public const int SUFFIX_LENGTH = 5;

    public const string SUFFIX_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

    public static function for(string $nickname): string
    {
        return self::base($nickname).'-'.self::suffix();
    }

    /**
     * The stable, human-written half of the slug — indexed on its own column so
     * "every link staff called `discord`" stays a cheap query.
     */
    public static function base(string $nickname): string
    {
        return Str::slug($nickname);
    }

    public static function suffix(): string
    {
        $alphabet = self::SUFFIX_ALPHABET;
        $lastIndex = mb_strlen($alphabet) - 1;
        $suffix = '';

        for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
            $suffix .= $alphabet[random_int(0, $lastIndex)];
        }

        return $suffix;
    }
}
