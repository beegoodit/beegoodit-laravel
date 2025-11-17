<?php

namespace BeeGoodIT\FilamentUserProfile;

use Illuminate\Support\ServiceProvider;

class FilamentUserProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-user-profile');
    }

    public function register(): void
    {
        // Register facade
        $this->app->singleton('filament-user-profile', function ($app) {
            return new UserProfileHelper;
        });
    }
}

