<?php

declare(strict_types=1);

namespace Heart\Provider\Domain\Repositories;

use Heart\Autentication\DTO\OAuthAccessDTO;
use Heart\Provider\Infrastructure\Models\Token;

interface TokenRepository
{
    public function create(string $providerId, OAuthAccessDTO $credentials): Token;
}
