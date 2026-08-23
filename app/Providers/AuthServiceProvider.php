<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use He4rt\Identity\ExternalIdentity\Models\ExternalIdentity;
use He4rt\Identity\ExternalIdentity\Policies\ExternalIdentityPolicy;
use He4rt\Identity\User\Models\User;
use He4rt\Identity\User\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        ExternalIdentity::class => ExternalIdentityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

    }
}
