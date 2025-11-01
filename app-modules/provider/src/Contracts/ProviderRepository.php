<?php

declare(strict_types=1);

namespace Heart\Provider\Contracts;

use Heart\Provider\DTO\NewProviderDTO;
use Heart\Provider\Entities\ProviderEntity;

interface ProviderRepository
{
    public function findByProvider(string $provider, string $providerId): ProviderEntity;

    public function findByProviderId(string $providerId): ?ProviderEntity;

    public function getProvider(string $provider, string $providerId): ?ProviderEntity;

    public function create(string $userId, NewProviderDTO $providerDTO): ProviderEntity;
}
