<?php

declare(strict_types=1);

use He4rt\Feedback\Http\Controllers\FeedbacksController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['api', 'bot-auth'])->group(function (): void {
    Route::get('feedbacks/{feedbackId}', [FeedbacksController::class, 'getFeedback'])
        ->name('feedbacks.show');
    Route::post('feedbacks', [FeedbacksController::class, 'postFeedback'])
        ->name('feedbacks.create');
    Route::post('feedbacks/{feedbackId}/{action}', [FeedbacksController::class, 'postReview'])
        ->name('feedbacks.review');
});
