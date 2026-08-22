<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Support;

use He4rt\Marketing\ShortLink\Exceptions\InvalidDestinationUrl;

/**
 * Single owner of the "is this URL redirectable?" rule.
 *
 * It lives in the domain (not in the Filament form) because both write Actions
 * are callable from outside the panel — a console command, a future bot command
 * or a test can reach them without ever touching a form request.
 */
final class DestinationUrlValidator
{
    /** @var list<string> */
    public const array ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * @throws InvalidDestinationUrl
     */
    public static function assert(string $url): string
    {
        $url = mb_trim($url);

        if ($url === '') {
            throw InvalidDestinationUrl::malformed($url);
        }

        $parts = parse_url($url);

        if ($parts === false || !isset($parts['host']) || $parts['host'] === '') {
            throw InvalidDestinationUrl::malformed($url);
        }

        $scheme = isset($parts['scheme']) ? mb_strtolower($parts['scheme']) : null;

        if ($scheme === null || !in_array($scheme, self::ALLOWED_SCHEMES, strict: true)) {
            throw InvalidDestinationUrl::unsupportedScheme($url, $scheme);
        }

        return $url;
    }

    public static function isValid(string $url): bool
    {
        try {
            self::assert($url);

            return true;
        } catch (InvalidDestinationUrl) {
            return false;
        }
    }
}
