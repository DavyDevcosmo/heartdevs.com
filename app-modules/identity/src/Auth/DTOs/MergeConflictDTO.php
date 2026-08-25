<?php

declare(strict_types=1);

namespace He4rt\Identity\Auth\DTOs;

use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;

final readonly class MergeConflictDTO
{
    public function __construct(
        public string $conflictingUserId,
        public IdentityProvider $provider,
        public OAuthAccessDTO $credentials,
        public OAuthUserDTO $oauthUser,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toSession(): array
    {
        $credentials = $this->credentials->toClientAccessManager();

        return [
            'conflicting_user_id' => $this->conflictingUserId,
            'provider' => $this->provider->value,
            'credentials' => [
                'encrypted' => true,
                'access_token' => $credentials->accessToken,
                'refresh_token' => $credentials->refreshToken,
                'expires_in' => $credentials->expiresIn,
            ],
            'oauth_user' => [
                'provider_id' => $this->oauthUser->providerId,
                'username' => $this->oauthUser->username,
                'name' => $this->oauthUser->name,
                'email' => $this->oauthUser->email,
                'avatar_url' => $this->oauthUser->avatarUrl,
                'metadata' => $this->oauthUser->toMetadata(),
            ],
        ];
    }
}
