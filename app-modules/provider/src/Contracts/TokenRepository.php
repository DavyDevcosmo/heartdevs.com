<?php

declare(strict_types=1);

namespace Heart\Provider\Contracts;

use Heart\Authentication\DTO\OAuthAccessDTO;
use Heart\Provider\Models\Token;

interface TokenRepository
{
    public function create(string $providerId, OAuthAccessDTO $credentials): Token;
}
