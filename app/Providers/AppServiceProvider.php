<?php

namespace App\Providers;

use App\Services\SettingService;
use App\View\Composers\HeaderComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(SettingService $settingService): void
    {
        Gate::define('manage-users', function ($user) {
            return $user->hasRole('Super Admin');
        });

        View::composer(
            ['partials.header', 'partials.sidebar'],
            HeaderComposer::class
        );

        View::share(
            'settings',
            $settingService->get()
        );
    }
}
