<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;

use App\Enums\RoleEnum;
use App\Models\Position;
use App\Observers\PositionObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleEnum::SUPER_ADMIN->value) ? true : null;
        });
        Schema::defaultStringLength(191);
        // Register model observers
        Position::observe(PositionObserver::class);

        // if ($this->app->environment('production') || $this->app->environment('local')) {
        //     URL::forceScheme('https');
        // }
    }
}