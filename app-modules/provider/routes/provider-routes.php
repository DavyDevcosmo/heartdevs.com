<?php

declare(strict_types=1);

use App\Http\Middleware\BotAuthentication;
use He4rt\Provider\Http\Controller\ProvidersController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['api', BotAuthentication::class])->group(function (): void {
    Route::post('/providers/{provider}', [ProvidersController::class, 'postProvider'])
        ->name('providers.store');
});
