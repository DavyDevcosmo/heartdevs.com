<?php

declare(strict_types=1);

use App\Http\Middleware\BotAuthentication;
use He4rt\Meeting\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['api', BotAuthentication::class])->group(function (): void {
    Route::prefix('events/{provider}')->name('events.')->group(function (): void {
        Route::prefix('meeting')->name('meeting.')->group(function (): void {
            Route::get('/', [MeetingController::class, 'getMeetings'])->name('getMeetings');
            Route::post('/start', [MeetingController::class, 'postMeeting'])->name('postMeeting');
            Route::post('/end', [MeetingController::class, 'postEndMeeting'])->name('postEndMeeting');
        });
    });
});
