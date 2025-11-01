<?php

declare(strict_types=1);

use Heart\Authentication\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->middleware('web')
    ->group(function (): void {
        Route::prefix('oauth')->group(function (): void {
            Route::get('/{provider}', [OAuthController::class, 'getAuthenticate']);
            Route::get('/{provider}/redirect', [OAuthController::class, 'getRedirect']);
        });
    });
