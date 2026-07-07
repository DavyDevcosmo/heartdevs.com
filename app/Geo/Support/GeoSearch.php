<?php

declare(strict_types=1);

namespace App\Geo\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class GeoSearch
{
    /**
     * @param  Builder<covariant Model>  $query
     */
    public static function likeOperator(Builder $query): string
    {
        /** @var Connection $connection */
        $connection = $query->getConnection();

        return $connection->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

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
