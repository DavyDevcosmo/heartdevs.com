<?php

declare(strict_types=1);

namespace Heart\Authentication\Contracts;

use Heart\Authentication\DTO\OAuthAccessDTO;
use Heart\Authentication\DTO\OAuthUserDTO;

interface OAuthClientContract
{
    public function redirectUrl(): string;

    public function auth(string $code): OAuthAccessDTO;

    public function getAuthenticatedUser(OAuthAccessDTO $credentials): OAuthUserDTO;
}
