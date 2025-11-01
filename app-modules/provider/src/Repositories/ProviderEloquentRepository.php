<?php

declare(strict_types=1);

namespace He4rt\Provider\Repositories;

use He4rt\Provider\Contracts\ProviderRepository;
use He4rt\Provider\DTO\NewProviderDTO;
use He4rt\Provider\Entities\ProviderEntity;
use He4rt\Provider\Exceptions\ProviderException;
use He4rt\Provider\Models\Provider;

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
