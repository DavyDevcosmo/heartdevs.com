<?php

declare(strict_types=1);

namespace App\Geo\Support;

use Illuminate\Support\Str;

final class GeoSearch
{
    public static function matchesTerm(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return str_contains(
            Str::ascii(mb_strtolower($haystack)),
            Str::ascii(mb_strtolower($needle)),
        );
    }
}
