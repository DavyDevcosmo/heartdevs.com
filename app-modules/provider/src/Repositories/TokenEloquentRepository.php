<?php

declare(strict_types=1);

namespace He4rt\Provider\Repositories;

use He4rt\Authentication\DTO\OAuthAccessDTO;
use He4rt\Provider\Contracts\TokenRepository;
use He4rt\Provider\Models\Token;

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
