<?php

declare(strict_types=1);

namespace He4rt\User\Actions;

use He4rt\User\Contracts\UserRepository;
use He4rt\User\Entities\UserEntity;
use He4rt\User\Exceptions\UserEntityException;

final readonly class GetUser
{
    public function __construct(private UserRepository $repository) {}

    /** @throws UserEntityException */
    public function handle(string $userId): UserEntity
    {
        return $this->repository->find($userId);
    }
}
