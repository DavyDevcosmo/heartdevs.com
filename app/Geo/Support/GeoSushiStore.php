<?php

declare(strict_types=1);

namespace App\Geo\Support;

use Illuminate\Support\Facades\Cache;

final class GeoSushiStore
{
    public const string COUNTRIES_KEY = 'geo.sushi.countries';

    public const string STATES_KEY = 'geo.sushi.states';

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public static function mergeCountries(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $existing = collect(self::countries())->keyBy('id');

        foreach ($rows as $row) {
            $id = (int) $row['id'];

            $existing->put($id, [
                'id' => $id,
                'iso2' => (string) ($row['iso2'] ?? ''),
                'iso3' => isset($row['iso3']) ? (string) $row['iso3'] : null,
                'name' => (string) ($row['name'] ?? ''),
            ]);
        }

        Cache::forever(self::COUNTRIES_KEY, $existing->values()->all());
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public static function mergeStates(string $countryCode, array $rows, ?int $countryId = null): void
    {
        if ($rows === []) {
            return;
        }

        $countryCode = mb_strtoupper($countryCode);
        $existing = collect(self::states())->keyBy('id');

        foreach ($rows as $row) {
            $id = (int) $row['id'];

            $existing->put($id, [
                'id' => $id,
                'country_id' => $countryId ?? (int) ($row['country_id'] ?? 0),
                'country_code' => $countryCode,
                'state_code' => isset($row['state_code']) ? (string) $row['state_code'] : null,
                'name' => (string) ($row['name'] ?? ''),
            ]);
        }

        Cache::forever(self::STATES_KEY, $existing->values()->all());
    }

    public static function hasCountries(): bool
    {
        return self::countries() !== [];
    }

    public static function hasStatesFor(string $countryCode): bool
    {
        $countryCode = mb_strtoupper($countryCode);

        return collect(self::states())->contains(
            static fn (array $state): bool => ($state['country_code'] ?? '') === $countryCode,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function countries(): array
    {
        /** @var list<array<string, mixed>> */
        return Cache::get(self::COUNTRIES_KEY, []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function states(): array
    {
        /** @var list<array<string, mixed>> */
        return Cache::get(self::STATES_KEY, []);
    }
}
