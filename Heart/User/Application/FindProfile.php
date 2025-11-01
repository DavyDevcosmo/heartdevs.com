<?php

declare(strict_types=1);

namespace Heart\User\Application;

use Heart\Provider\Entities\ProviderEntity;
use Heart\Provider\Repositories\ProviderRepository;
use Heart\User\Application\Exceptions\ProfileException;
use Heart\User\Domain\Actions\GetProfile;
use Heart\User\Domain\Entities\ProfileEntity;
use Heart\User\Domain\Entities\UserEntity;
use Heart\User\Domain\Repositories\UserRepository;

final readonly class FindProfile
{
    public function __construct(
        private GetProfile $profile,
        private UserRepository $userRepository,
        private ProviderRepository $providerRepository,
    ) {}

    /**
     * @throws ProfileException
     */
    public function handle(string $value): ProfileEntity
    {
        $userEntity = $this->userRepository->findByUsername($value);

        if ($userEntity instanceof UserEntity) {
            return $this->profile->handle($userEntity->id);
        }

        $providerEntity = $this->providerRepository->findByProviderId($value);

        if ($providerEntity instanceof ProviderEntity) {
            return $this->profile->handle($providerEntity->userId);
        }

        throw ProfileException::notFound();
    }
}
