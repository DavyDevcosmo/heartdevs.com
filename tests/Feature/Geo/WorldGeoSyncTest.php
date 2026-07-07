<?php

declare(strict_types=1);

use App\Geo\Jobs\SyncWorldCitiesJob;
use App\Geo\Jobs\SyncWorldCountriesJob;
use App\Geo\Jobs\SyncWorldStatesJob;
use App\Geo\Models\GeoCity;
use App\Geo\Models\GeoCountry;
use App\Geo\Models\GeoState;
use App\Geo\Support\GeoSelect;
use App\Geo\Support\GeoSushiStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config(['geo.world_api_url' => 'https://world.test/api']);

    Cache::forget(GeoSushiStore::COUNTRIES_KEY);
    Cache::forget(GeoSushiStore::STATES_KEY);
});

it('syncs countries from world api on search', function (): void {
    Http::fake([
        'https://world.test/api/countries*' => Http::response([
            'success' => true,
            'data' => [
                ['id' => 31, 'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil'],
            ],
        ]),
    ]);

    dispatch_sync(new SyncWorldCountriesJob('bra'));

    expect(GeoCountry::query()->count())->toBe(1)
        ->and(GeoCountry::query()->first())
        ->iso2->toBe('BR')
        ->name->toBe('Brazil')
        ->and(GeoSushiStore::countries())->toHaveCount(1);
});

it('syncs states for a country only once', function (): void {
    GeoSushiStore::mergeCountries([
        ['id' => 31, 'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil'],
    ]);

    Http::fake([
        'https://world.test/api/states*' => Http::response([
            'success' => true,
            'data' => [
                ['id' => 509, 'name' => 'São Paulo'],
                ['id' => 485, 'name' => 'Acre'],
            ],
        ]),
    ]);

    dispatch_sync(new SyncWorldStatesJob('BR'));
    dispatch_sync(new SyncWorldStatesJob('BR'));

    Http::assertSentCount(1);

    expect(GeoState::query()->count())->toBe(2)
        ->and(GeoState::query()->where('name', 'São Paulo')->exists())->toBeTrue();
});

it('syncs cities on search for a state', function (): void {
    GeoSushiStore::mergeCountries([
        ['id' => 31, 'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil'],
    ]);

    GeoSushiStore::mergeStates('BR', [
        ['id' => 509, 'name' => 'São Paulo'],
    ], 31);

    Http::fake([
        'https://world.test/api/cities*' => Http::response([
            'success' => true,
            'data' => [
                ['id' => 15_542, 'name' => 'São Paulo'],
            ],
        ]),
    ]);

    dispatch_sync(new SyncWorldCitiesJob('BR', 'São Paulo', 'sao'));

    expect(GeoCity::query()->count())->toBe(1)
        ->and(GeoCity::query()->first())
        ->name->toBe('São Paulo')
        ->state_id->toBe(509);
});

it('resolves country label for location preview', function (): void {
    GeoSushiStore::mergeCountries([
        ['id' => 31, 'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil'],
    ]);

    expect(GeoSelect::formatLocation('São Paulo', 'São Paulo', 'BR'))
        ->toBe('São Paulo, São Paulo, Brazil');
});

it('searches countries via geo select helper', function (): void {
    Http::fake([
        'https://world.test/api/countries*' => Http::response([
            'success' => true,
            'data' => [
                ['id' => 31, 'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil'],
            ],
        ]),
    ]);

    $options = GeoSelect::searchCountries('bra');

    expect($options)->toBe(['BR' => 'Brazil']);
});

it('searches cities ignoring accents in local filter', function (): void {
    GeoSushiStore::mergeStates('BR', [
        ['id' => 509, 'name' => 'São Paulo'],
    ], 31);

    GeoCity::query()->create([
        'id' => 15_542,
        'state_id' => 509,
        'country_code' => 'BR',
        'name' => 'São Paulo',
    ]);

    Http::fake();

    $options = GeoSelect::searchCities('BR', 'São Paulo', 'sao');

    expect($options)->toBe(['São Paulo' => 'São Paulo']);
});

it('lists cached cities for a state without searching', function (): void {
    GeoSushiStore::mergeStates('BR', [
        ['id' => 508, 'name' => 'Santa Catarina'],
    ], 31);

    GeoCity::query()->create([
        'id' => 1,
        'state_id' => 508,
        'country_code' => 'BR',
        'name' => 'Jaraguá do Sul',
    ]);

    GeoCity::query()->create([
        'id' => 2,
        'state_id' => 508,
        'country_code' => 'BR',
        'name' => 'Florianópolis',
    ]);

    Http::fake();

    expect(GeoSelect::cachedCities('BR', 'Santa Catarina'))
        ->toBe([
            'Florianópolis' => 'Florianópolis',
            'Jaraguá do Sul' => 'Jaraguá do Sul',
        ]);
});
