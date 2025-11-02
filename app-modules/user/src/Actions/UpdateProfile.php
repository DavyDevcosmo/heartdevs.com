<?php

declare(strict_types=1);

namespace He4rt\User\Actions;

use He4rt\User\Contracts\UserRepository;

final readonly class UpdateProfile
{
    public function __construct(
        private FindProfile $findProfile,
        private UserRepository $userRepository
    ) {}

    public function handle(string $value, array $payload): void
    {
        $profileEntity = $this->findProfile->handle($value);
        $profileEntity->informationEntity->update($payload['info']);

        $this->userRepository->updateProfile($profileEntity);
    }
}
