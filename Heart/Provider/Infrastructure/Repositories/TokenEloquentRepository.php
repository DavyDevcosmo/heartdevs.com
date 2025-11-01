<?php

declare(strict_types=1);

namespace Heart\Provider\Infrastructure\Repositories;

use Heart\Autentication\DTO\OAuthAccessDTO;
use Heart\Provider\Domain\Repositories\TokenRepository;
use Heart\Provider\Infrastructure\Models\Token;

final class TokenEloquentRepository implements TokenRepository
{
    public function create(string $providerId, OAuthAccessDTO $credentials): Token
    {
        return Token::query()->create([
            'provider_id' => $providerId,
            ...$credentials->toDatabase(),
        ]);
    }
}
