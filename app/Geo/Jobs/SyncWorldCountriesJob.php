<?php

declare(strict_types=1);

namespace App\Geo\Jobs;

use App\Geo\Support\GeoSushiStore;
use App\Geo\WorldApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncWorldCountriesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly ?string $search = null,
    ) {}

    public function handle(WorldApiClient $api): void
    {
        $search = filled($this->search) ? $this->search : null;

        if ($search === null && GeoSushiStore::hasCountries()) {
            return;
        }

        GeoSushiStore::mergeCountries($api->countries($search));
    }
}
