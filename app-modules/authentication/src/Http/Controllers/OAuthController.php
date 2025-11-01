<?php

declare(strict_types=1);

namespace Heart\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Heart\Autentication\Services\OAuthService;
use Heart\Authentication\OAuth\Domain\Actions\RedirectOAuthUrl;
use Illuminate\Http\RedirectResponse;

final class OAuthController extends Controller
{
    public function getRedirect(string $provider, RedirectOAuthUrl $action): RedirectResponse
    {
        return redirect()->to($action->handle($provider));
    }

    public function getAuthenticate(string $provider, OAuthService $action): RedirectResponse
    {
        $action->handle($provider, request()->input('code'));

        return redirect()->intended('/profile');
    }
}
