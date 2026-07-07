<?php

declare(strict_types=1);

namespace App\Geo;

use Illuminate\Support\Facades\Http;

final readonly class WorldApiClient
{
    public function __construct(
        private string $baseUrl,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function countryByIso2(string $iso2): array
    {
        return $this->get('/countries', [
            'fields' => 'iso2,iso3',
            'filters' => ['iso2' => mb_strtoupper($iso2)],
        ]);
    }

    /**
     * @param  list<string>  $fields
     * @return list<array<string, mixed>>
     */
    public function countries(?string $search = null, array $fields = ['iso2', 'iso3']): array
    {
        return $this->get('/countries', array_filter([
            'search' => $search,
            'fields' => implode(',', $fields),
        ]));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function states(string $countryCode, ?string $search = null): array
    {
        return $this->get('/states', array_filter([
            'filters' => ['country_code' => $countryCode],
            'search' => $search,
        ]));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function cities(string $countryCode, ?int $stateId = null, ?string $search = null): array
    {
        $filters = ['country_code' => $countryCode];

        if ($stateId !== null) {
            $filters['state_id'] = $stateId;
        }

        return $this->get('/cities', array_filter([
            'filters' => $filters,
            'search' => $search,
        ]));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function get(string $path, array $query = []): array
    {
        $response = Http::baseUrl(mb_rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->get($path, $query);

        $response->throw();

        /** @var list<array<string, mixed>> $data */
        $data = $response->json('data', []);

        return $data;
    }
}
