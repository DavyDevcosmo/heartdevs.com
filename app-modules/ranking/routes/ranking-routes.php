<?php

declare(strict_types=1);

use Heart\Ranking\Actions\RankingByLevel;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api')
    ->group(function (): void {
        Route::get('/ranking/leveling', [RankingByLevel::class, 'handle'])->name('ranking.leveling');
    });
