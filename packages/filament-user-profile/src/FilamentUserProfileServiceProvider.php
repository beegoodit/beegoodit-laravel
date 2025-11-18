<?php

namespace BeeGoodIT\FilamentUserProfile;

use BeeGoodIT\FilamentUserProfile\Filament\UserProfilePanelProvider;
use Illuminate\Support\ServiceProvider;

class FilamentUserProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-user-profile');

        // Publish timezone GeoJSON data
        $this->publishes([
            __DIR__.'/../public/data/timezones-tiny.geojson' => public_path('data/timezones-tiny.geojson'),
        ], 'filament-user-profile-timezone-data');

        // User profile pages are registered via UserProfilePanelProvider
        // This creates a separate panel at /settings without tenancy
    }

    public function register(): void
    {
        // Register facade
        $this->app->singleton('filament-user-profile', function ($app) {
            return new UserProfileHelper;
        });
        
        // Register the user profile panel provider
        $this->app->register(UserProfilePanelProvider::class);
    }
}

