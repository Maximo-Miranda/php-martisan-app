<?php

namespace App\Providers;

use App\Listeners\CreateDefaultProjectListener;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Register event listener for creating default project on email verification
        Event::listen(Verified::class, CreateDefaultProjectListener::class);

        // Super Admin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
