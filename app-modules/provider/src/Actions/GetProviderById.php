<?php

declare(strict_types=1);

namespace He4rt\Provider\Actions;

use He4rt\Provider\Contracts\ProviderRepository;
use He4rt\Provider\Entities\ProviderEntity;

final readonly class GetProviderById
{
    public function __construct(private ProviderRepository $providerRepository) {}

    public function handle(string $provider, string $providerId): ProviderEntity
    {
        return $this->providerRepository->findByProvider($provider, $providerId);
    }
}
