<?php

declare(strict_types=1);

namespace App\Geo\Support;

use App\Geo\Jobs\SyncWorldCitiesJob;
use App\Geo\Jobs\SyncWorldCountriesJob;
use App\Geo\Jobs\SyncWorldStatesJob;
use App\Geo\Models\GeoCity;
use App\Geo\Models\GeoCountry;
use App\Geo\Models\GeoState;
use App\Geo\WorldApiClient;

final class GeoSelect
{
    /**
     * @return array<string, string>
     */
    public static function searchCountries(?string $search): array
    {
        $term = mb_trim((string) $search);

        if ($term !== '') {
            dispatch_sync(new SyncWorldCountriesJob($term));
        } elseif (!GeoSushiStore::hasCountries()) {
            dispatch_sync(new SyncWorldCountriesJob());
        }

        $query = GeoCountry::query()->orderBy('name');

        if ($term !== '') {
            $query->matching($term);
        }

        return $query
            ->limit(50)
            ->pluck('name', 'iso2')
            ->all();
    }

    public static function countryLabel(?string $iso2): ?string
    {
        if (blank($iso2)) {
            return null;
        }

        $iso2 = mb_strtoupper($iso2);

        $name = GeoCountry::query()->where('iso2', $iso2)->value('name');

        if ($name !== null) {
            return $name;
        }

        $rows = resolve(WorldApiClient::class)->countryByIso2($iso2);
        $row = $rows[0] ?? null;

        if ($row === null) {
            return null;
        }

        GeoSushiStore::mergeCountries([$row]);

        return (string) ($row['name'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    public static function statesForCountry(?string $countryCode): array
    {
        if (blank($countryCode)) {
            return [];
        }

        $countryCode = mb_strtoupper($countryCode);

        dispatch_sync(new SyncWorldStatesJob($countryCode));

        return GeoState::query()
            ->where('country_code', $countryCode)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function cachedCities(?string $countryCode, ?string $stateName): array
    {
        return self::searchCities($countryCode, $stateName, '');
    }

    /**
     * @return array<string, string>
     */
    public static function searchCities(?string $countryCode, ?string $stateName, ?string $search): array
    {
        if (blank($countryCode) || blank($stateName)) {
            return [];
        }

        $countryCode = mb_strtoupper($countryCode);
        $term = mb_trim((string) $search);

        $state = GeoState::query()
            ->where('country_code', $countryCode)
            ->where('name', $stateName)
            ->first();

        if ($state === null) {
            return [];
        }

        if ($term !== '') {
            dispatch_sync(new SyncWorldCitiesJob($countryCode, $stateName, $term));
        }

        return GeoCity::query()
            ->where('country_code', $countryCode)
            ->where('state_id', $state->id)
            ->orderBy('name')
            ->get()
            ->filter(static fn (GeoCity $city): bool => GeoSearch::matchesTerm($city->name, $term))
            ->take(50)
            ->mapWithKeys(static fn (GeoCity $city): array => [$city->name => $city->name])
            ->all();
    }

    public static function formatLocation(?string $city, ?string $state, ?string $country): ?string
    {
        $countryLabel = filled($country)
            ? (self::countryLabel($country) ?? $country)
            : null;

        $location = collect([$city, $state, $countryLabel])
            ->filter()
            ->implode(', ');

        return $location !== '' ? $location : null;
    }
}
