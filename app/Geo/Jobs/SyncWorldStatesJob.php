<?php

declare(strict_types=1);

namespace App\Geo\Jobs;

use App\Geo\Models\GeoCountry;
use App\Geo\Support\GeoSushiStore;
use App\Geo\WorldApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncWorldStatesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $countryCode,
    ) {}

    public function handle(WorldApiClient $api): void
    {
        $countryCode = mb_strtoupper($this->countryCode);

        if (GeoSushiStore::hasStatesFor($countryCode)) {
            return;
        }

        $countryId = $this->ensureCountryExists($api, $countryCode);

        GeoSushiStore::mergeStates(
            $countryCode,
            $api->states($countryCode),
            $countryId,
        );
    }

    private function ensureCountryExists(WorldApiClient $api, string $countryCode): ?int
    {
        $existingId = GeoCountry::query()->where('iso2', $countryCode)->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $rows = $api->countryByIso2($countryCode);
        $row = $rows[0] ?? null;

        if ($row === null) {
            return null;
        }

        GeoSushiStore::mergeCountries([$row]);

        return (int) $row['id'];
    }
}
