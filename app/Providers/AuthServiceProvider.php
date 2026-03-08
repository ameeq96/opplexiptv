<?php

namespace App\Providers;

use App\Models\Digital\DigitalOrder;
use App\Policies\DigitalOrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DigitalOrder::class => DigitalOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('manage-blogs', function ($user = null) {
            return $user !== null;
        });

        Gate::define('manage-digital-commerce', function ($user = null) {
            return $user instanceof \App\Models\Admin;
        });
    }
}
