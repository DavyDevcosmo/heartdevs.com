<?php

declare(strict_types=1);

namespace Heart\Provider\Actions;

use Heart\Provider\Contracts\ProviderRepository;
use Heart\Provider\Entities\ProviderEntity;

final readonly class GetProviderById
{
    public function __construct(private ProviderRepository $providerRepository) {}

    public function handle(string $provider, string $providerId): ProviderEntity
    {
        return $this->providerRepository->findByProvider($provider, $providerId);
    }
}
