<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\View\Composers\NotificationComposer;
use Illuminate\Support\Facades\View;

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
        Gate::before(function ($user, $ability) {
            // super-admin berlaku di semua app yang memakai tabel auth bersama
            return $user->hasRole(config('access.super_admin_role')) ? true : null;
        });

        View::composer('layouts.dashboard.navbar', NotificationComposer::class);
    }
}
