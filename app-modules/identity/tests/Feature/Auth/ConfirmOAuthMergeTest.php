<?php

declare(strict_types=1);

use He4rt\Identity\Auth\Actions\ConfirmOAuthMerge;
use He4rt\Identity\Auth\DTOs\PendingOAuthMergeDTO;
use He4rt\Identity\ExternalIdentity\Data\ClientAccessManager;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use He4rt\Identity\ExternalIdentity\Events\ExternalIdentityConnected;
use He4rt\Identity\ExternalIdentity\Models\ExternalIdentity;
use He4rt\Identity\User\Models\User;
use Illuminate\Support\Facades\Event;

test('rolls back the account merge when the oauth connection cannot finish', function (): void {
    $currentUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $discordIdentity = ExternalIdentity::factory()->create([
        'model_type' => (new User)->getMorphClass(),
        'model_id' => $targetUser->id,
        'provider' => IdentityProvider::Discord,
        'external_account_id' => 'discord-rollback',
        'credentials' => ClientAccessManager::make(),
        'connected_at' => null,
        'metadata' => ['username' => 'imported-user'],
    ]);
    $currentIdentity = ExternalIdentity::factory()->create([
        'model_type' => (new User)->getMorphClass(),
        'model_id' => $currentUser->id,
        'provider' => IdentityProvider::GitHub,
        'external_account_id' => 'github-rollback',
    ]);
    $pending = new PendingOAuthMergeDTO(
        conflictingUserId: $targetUser->id,
        provider: IdentityProvider::Discord,
        providerId: 'discord-rollback',
        credentials: [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3_600,
        ],
        metadata: ['username' => 'oauth-user'],
    );

    Event::listen(
        ExternalIdentityConnected::class,
        static fn () => throw new RuntimeException('connection listener failed'),
    );

    expect(fn () => resolve(ConfirmOAuthMerge::class)->execute($currentUser, $pending))
        ->toThrow(RuntimeException::class, 'connection listener failed');

    $discordIdentity->refresh();
    $currentIdentity->refresh();

    expect(User::query()->find($currentUser->id))->not->toBeNull()
        ->and(User::query()->find($targetUser->id))->not->toBeNull()
        ->and($discordIdentity->model_id)->toBe($targetUser->id)
        ->and($discordIdentity->metadata)->toBe(['username' => 'imported-user'])
        ->and($discordIdentity->credentials->getAccessToken())->toBeNull()
        ->and($discordIdentity->connected_at)->toBeNull()
        ->and($currentIdentity->model_id)->toBe($currentUser->id);
});

test('rejects an incomplete pending oauth merge payload', function (): void {
    expect(PendingOAuthMergeDTO::fromSession([
        'conflicting_user_id' => 'target-user',
        'provider' => IdentityProvider::Discord->value,
        'credentials' => ['access_token' => 'missing-refresh-token'],
        'oauth_user' => ['provider_id' => 'discord-id'],
    ]))->toBeNull();
});

test('keeps compatibility with pending oauth sessions created before metadata was embedded', function (): void {
    $pending = PendingOAuthMergeDTO::fromSession([
        'conflicting_user_id' => 'target-user',
        'provider' => IdentityProvider::Discord->value,
        'credentials' => [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3_600,
        ],
        'oauth_user' => [
            'provider_id' => 'discord-id',
            'username' => 'discord-user',
            'name' => 'Discord User',
            'email' => null,
            'avatar_url' => null,
        ],
    ]);

    expect($pending)->toBeInstanceOf(PendingOAuthMergeDTO::class)
        ->and($pending?->metadata)->toBe([
            'username' => 'discord-user',
            'global_name' => 'Discord User',
        ]);
});
