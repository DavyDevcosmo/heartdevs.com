<?php

declare(strict_types=1);

namespace Heart\Autentication\Services;

use GuzzleHttp\Exception\ClientException;
use He4rt\Authentication\Enums\OAuthProviderEnum;
use He4rt\Authentication\OAuth\Application\SubscriberDTO;
use He4rt\Authentication\OAuth\Application\SubscribersRepository;
use He4rt\Provider\Contracts\ProviderRepository;
use He4rt\Provider\Contracts\TokenRepository;
use Illuminate\Support\Facades\Auth;
use Kingdom\Authentication\OAuth\Domain\Actions\GetOAuthUser;

final readonly class OAuthService
{
    public function __construct(
        private GetOAuthUser $getUserAction,
        private ProviderRepository $providerRepository,
        private TokenRepository $tokenRepository,
        private SubscribersRepository $subscribersRepository
    ) {
    }

    public function handle(string $provider, string $code)
    {
        try {
            $providerUser = $this->getUserAction->handle($provider, $code);
        } catch (ClientException $clientException) {
            if (str_contains($clientException->getMessage(), 'Invalid authorization code')) {
                $OAuthProviderEnum = OAuthProviderEnum::from($provider);

                return redirect()->to($OAuthProviderEnum->getProvider()->redirectUrl());
            }

            throw $clientException;
        }

        $persistedProvider = $this->providerRepository->findByProvider($providerUser);

        if ($persistedProvider) {
            $this->tokenRepository->create($persistedProvider->getKey(), $providerUser->credentials);
            Auth::login($persistedProvider->subscriber);

            return;
        }

        $subscriberId = Auth::check()
            ? Auth::id()
            : $this->subscribersRepository->create(SubscriberDTO::makeFromOAuth($providerUser))->getKey();

        $persistedProvider = $this->providerRepository->create($subscriberId, $providerUser);
        $this->tokenRepository->create($persistedProvider->getKey(), $providerUser->credentials);

        Auth::login($persistedProvider->subscriber);
    }
}
