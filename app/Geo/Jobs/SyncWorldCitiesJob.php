<?php

declare(strict_types=1);

namespace App\Geo\Jobs;

use App\Geo\Models\GeoCity;
use App\Geo\Models\GeoState;
use App\Geo\Support\GeoSearch;
use App\Geo\WorldApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;

final class SyncWorldCitiesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $countryCode,
        private readonly string $stateName,
        private readonly ?string $search = null,
    ) {}

    public function handle(WorldApiClient $api): void
    {
        $countryCode = mb_strtoupper($this->countryCode);
        $search = filled($this->search) ? $this->search : null;

        if ($search === null) {
            return;
        }

        $state = GeoState::query()
            ->where('country_code', $countryCode)
            ->where('name', $this->stateName)
            ->first();

        if ($state === null) {
            return;
        }

        $existingCount = GeoCity::query()
            ->where('country_code', $countryCode)
            ->where('state_id', $state->id)
            ->get()
            ->filter(static fn (GeoCity $city): bool => GeoSearch::matchesTerm($city->name, $search))
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $rows = $api->cities($countryCode, $state->id, $search);
        $syncedAt = Date::now();

        foreach ($rows as $row) {
            GeoCity::query()->updateOrCreate(
                ['id' => (int) $row['id']],
                [
                    'state_id' => $state->id,
                    'country_code' => $countryCode,
                    'name' => (string) ($row['name'] ?? ''),
                    'synced_at' => $syncedAt,
                ],
            );
        }
    }
}
