<?php

declare(strict_types=1);

use He4rt\Profile\Http\Controllers\PublicProfileController;
use Illuminate\Support\Facades\Route;

// Module routes are loaded outside the `web` group, so there is no
// SubstituteBindings middleware: the username arrives as a plain string and the
// controller resolves the User itself.
Route::get('/@{username}', PublicProfileController::class)
    ->where('username', '[A-Za-z0-9_.-]+')
    ->name('profile.public');
