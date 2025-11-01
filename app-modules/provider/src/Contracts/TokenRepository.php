<?php

declare(strict_types=1);

namespace He4rt\Provider\Contracts;

use He4rt\Authentication\DTO\OAuthAccessDTO;
use He4rt\Provider\Models\Token;

interface TokenRepository
{
    public function create(string $providerId, OAuthAccessDTO $credentials): Token;
}
