<?php

declare(strict_types=1);

namespace Heart\Provider\Repositories;

use Heart\Authentication\DTO\OAuthAccessDTO;
use Heart\Provider\Contracts\TokenRepository;
use Heart\Provider\Models\Token;

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
