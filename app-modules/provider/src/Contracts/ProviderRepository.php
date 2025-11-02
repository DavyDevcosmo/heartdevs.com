<?php

declare(strict_types=1);

namespace He4rt\Provider\Contracts;

use He4rt\Provider\DTO\NewProviderDTO;
use He4rt\Provider\Entities\ProviderEntity;

interface ProviderRepository
{
    public function findByProvider(string $provider, string $providerId): ProviderEntity;

    public function findByProviderId(string $providerId): ?ProviderEntity;

    public function getProvider(string $provider, string $providerId): ?ProviderEntity;

    public function create(string $userId, NewProviderDTO $providerDTO): ProviderEntity;
}
