<?php

declare(strict_types=1);

namespace He4rt\User\Actions;

use He4rt\User\Contracts\UserRepository;
use He4rt\User\Entities\ProfileEntity;

final readonly class GetProfile
{
    public function __construct(private UserRepository $userRepository) {}

    public function handle(string $userId): ProfileEntity
    {
        return $this->userRepository->findProfile($userId);
    }
}
