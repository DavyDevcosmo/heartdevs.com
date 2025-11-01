<?php

declare(strict_types=1);

namespace Heart\Provider\Repositories;

use Heart\Provider\Contracts\ProviderRepository;
use Heart\Provider\DTO\NewProviderDTO;
use Heart\Provider\Entities\ProviderEntity;
use Heart\Provider\Exceptions\ProviderException;
use Heart\Provider\Models\Provider;

final class ProviderEloquentRepository implements ProviderRepository
{
    public function findByProvider(string $provider, string $providerId): ProviderEntity
    {
        $model = Provider::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        throw_unless($model, ProviderException::notFound($provider, $providerId));

        return ProviderEntity::make($model->toArray());
    }

    public function create(string $userId, NewProviderDTO $providerDTO): ProviderEntity
    {
        $model = Provider::query()->create([
            'user_id' => $userId,
            ...$providerDTO->jsonSerialize(),
        ]);

        return ProviderEntity::make($model->toArray());
    }

    public function findByProviderId(string $providerId): ProviderEntity
    {
        $model = Provider::query()->where('provider_id', $providerId)->first();

        return ProviderEntity::make($model->toArray());
    }

    public function getProvider(string $provider, string $providerId): ?ProviderEntity
    {
        $model = Provider::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if (! $model) {
            return null;
        }

        return ProviderEntity::make($model->toArray());
    }
}
