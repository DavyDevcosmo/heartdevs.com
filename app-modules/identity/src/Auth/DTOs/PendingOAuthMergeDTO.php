<?php

declare(strict_types=1);

namespace He4rt\Identity\Auth\DTOs;

use He4rt\Identity\ExternalIdentity\Data\ClientAccessManager;
use He4rt\Identity\ExternalIdentity\Enums\CredentialsType;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use Illuminate\Support\Facades\Crypt;

final readonly class PendingOAuthMergeDTO
{
    /**
     * @param  array{access_token: string, refresh_token: string, expires_in: int|string|null, encrypted?: bool}  $credentials
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $conflictingUserId,
        public IdentityProvider $provider,
        public string $providerId,
        public array $credentials,
        public array $metadata,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromSession(array $payload): ?self
    {
        $provider = is_string($payload['provider'] ?? null)
            ? IdentityProvider::tryFrom($payload['provider'])
            : null;
        $credentials = $payload['credentials'] ?? null;
        $rawOAuthUser = $payload['oauth_user'] ?? null;
        $oauthUser = is_array($rawOAuthUser)
            ? self::stringKeyedArray($rawOAuthUser)
            : null;

        if (
            !is_string($payload['conflicting_user_id'] ?? null)
            || !$provider instanceof IdentityProvider
            || $provider->getCredentialsType() !== CredentialsType::OAuth2
            || !is_array($credentials)
            || !is_string($credentials['access_token'] ?? null)
            || !is_string($credentials['refresh_token'] ?? null)
            || $oauthUser === null
            || !is_string($oauthUser['provider_id'] ?? null)
        ) {
            return null;
        }

        $encrypted = ($credentials['encrypted'] ?? false) === true;
        $expiresIn = $credentials['expires_in'] ?? null;

        if (
            $expiresIn !== null
            && !is_int($expiresIn)
            && (!$encrypted || !is_string($expiresIn))
        ) {
            return null;
        }

        $rawMetadata = $oauthUser['metadata'] ?? null;

        if ($rawMetadata !== null && !is_array($rawMetadata)) {
            return null;
        }

        $metadata = is_array($rawMetadata)
            ? self::stringKeyedArray($rawMetadata)
            : self::legacyMetadata($provider, $oauthUser);

        if ($metadata === null) {
            return null;
        }

        return new self(
            conflictingUserId: $payload['conflicting_user_id'],
            provider: $provider,
            providerId: $oauthUser['provider_id'],
            credentials: [
                'access_token' => $credentials['access_token'],
                'refresh_token' => $credentials['refresh_token'],
                'expires_in' => $expiresIn,
                'encrypted' => $encrypted,
            ],
            metadata: $metadata,
        );
    }

    public function toClientAccessManager(): ClientAccessManager
    {
        if (($this->credentials['encrypted'] ?? false) === true) {
            return ClientAccessManager::make(
                accessToken: $this->credentials['access_token'],
                refreshToken: $this->credentials['refresh_token'],
                expiresIn: $this->credentials['expires_in'],
            );
        }

        return ClientAccessManager::make(
            accessToken: Crypt::encrypt($this->credentials['access_token']),
            refreshToken: Crypt::encrypt($this->credentials['refresh_token']),
            expiresIn: $this->credentials['expires_in'] !== null
                ? Crypt::encrypt((string) $this->credentials['expires_in'])
                : null,
        );
    }

    /**
     * @param  array<string, mixed>  $oauthUser
     * @return array<string, mixed>
     */
    private static function legacyMetadata(IdentityProvider $provider, array $oauthUser): array
    {
        return array_filter([
            'email' => $oauthUser['email'] ?? null,
            'avatar' => $oauthUser['avatar_url'] ?? null,
            'username' => $oauthUser['username'] ?? null,
            'global_name' => $provider === IdentityProvider::Discord
                ? ($oauthUser['name'] ?? null)
                : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<mixed>  $payload
     * @return array<string, mixed>|null
     */
    private static function stringKeyedArray(array $payload): ?array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                return null;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
