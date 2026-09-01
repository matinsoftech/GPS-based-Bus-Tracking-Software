<?php

namespace App\Providers;

use App\View\Composers\HeaderComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // User Management is restricted to the Super Admin role only.
        // This single gate is used both by the `can:manage-users` route
        // middleware (returns 403 for everyone else) and by the sidebar
        // filter that hides the menu item from non-Super-Admin roles.
        Gate::define('manage-users', function ($user) {
            return $user->hasRole('Super Admin');
        });

        // Provide real notification data (badge count + dropdown list) to the
        // header bell on every authenticated page, and the school branding
        // (logo + name) to the header and sidebar.
        View::composer(['partials.header', 'partials.sidebar'], HeaderComposer::class);
    }
}
