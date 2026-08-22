<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a destination URL does not qualify as a redirect target.
 *
 * The short link is a public 302 hop under our own domain: anything it points at
 * is executed by the visitor's browser with our domain as the referrer. Only
 * `http` and `https` are acceptable — `javascript:`, `data:` and `file:` would
 * turn the shortener into an XSS / local-file vector.
 */
final class InvalidDestinationUrl extends InvalidArgumentException
{
    public static function unsupportedScheme(string $url, ?string $scheme): self
    {
        return new self(sprintf(
            'Destination "%s" uses the unsupported scheme "%s". Only http and https are allowed.',
            $url,
            $scheme ?? '(none)',
        ));
    }

    public static function malformed(string $url): self
    {
        return new self(sprintf('Destination "%s" is not a parsable absolute URL.', $url));
    }
}
