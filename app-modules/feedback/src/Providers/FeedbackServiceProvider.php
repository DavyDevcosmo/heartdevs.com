<?php

namespace He4rt\Feedback\Providers;

use He4rt\Feedback\Contracts\FeedbackRepository;
use He4rt\Feedback\Repositories\FeedbackEloquentRepository;
use Illuminate\Support\ServiceProvider;

class FeedbackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FeedbackRepository::class, FeedbackEloquentRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/feedback-routes.php');
    }
}
