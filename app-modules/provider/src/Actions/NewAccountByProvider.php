<?php

declare(strict_types=1);

namespace Heart\Provider\Actions;

use Heart\Provider\Contracts\ProviderRepository;
use Heart\Provider\DTO\NewProviderDTO;
use Heart\Provider\Entities\ProviderEntity;
use Heart\Provider\Enums\ProviderEnum;
use Heart\User\Domain\Repositories\UserRepository;

class NewAccountByProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProviderRepository $providerRepository,
    ) {}

    public function handle(ProviderEnum $providerEnum, string $providerId, string $username): ProviderEntity
    {
        $existentProvider = $this->providerRepository->getProvider($providerEnum->value, $providerId);

        if ($existentProvider instanceof ProviderEntity) {
            return $existentProvider;
        }

        $userEntity = $this->userRepository->createUser($username);

        return $this->providerRepository->create($userEntity->id, new NewProviderDTO(
            provider: $providerEnum,
            providerId: $providerId
        ));
    }
}
