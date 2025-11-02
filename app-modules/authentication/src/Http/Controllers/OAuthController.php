<?php

declare(strict_types=1);

namespace He4rt\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use He4rt\Authentication\OAuth\Domain\Actions\RedirectOAuthUrl;
use Heart\Autentication\Services\OAuthService;
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
