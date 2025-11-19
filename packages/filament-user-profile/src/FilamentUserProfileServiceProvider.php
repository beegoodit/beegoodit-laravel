<?php

namespace BeeGoodIT\FilamentUserProfile;

use BeeGoodIT\FilamentUserProfile\Filament\UserProfilePanelProvider;
use BeeGoodIT\FilamentUserProfile\UserProfileHelper;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Features;

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

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/add_two_factor_columns_to_users_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_add_two_factor_columns_to_users_table.php'),
        ], 'filament-user-profile-migrations');

        // Check for 2FA columns early (this will log if missing)
        // Only check if Fortify 2FA is enabled
        if (Features::enabled(Features::twoFactorAuthentication())) {
            UserProfileHelper::hasTwoFactorColumns();
        }

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

