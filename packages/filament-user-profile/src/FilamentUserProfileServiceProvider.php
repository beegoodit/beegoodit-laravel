<?php

namespace BeeGoodIT\FilamentUserProfile;

use BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeeGoodIT\FilamentUserProfile\Filament\UserProfilePanelProvider;
use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\SocialiteUserConnected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Features;

class FilamentUserProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-user-profile');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-user-profile');

        // Register Blade components
        Blade::componentNamespace('BeeGoodIT\\FilamentUserProfile\\View\\Components', 'filament-user-profile');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-user-profile'),
        ], 'filament-user-profile-lang');

        // Publish timezone GeoJSON data
        $this->publishes([
            __DIR__ . '/../public/data/timezones-tiny.geojson' => public_path('data/timezones-tiny.geojson'),
        ], 'filament-user-profile-timezone-data');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/add_two_factor_columns_to_users_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_add_two_factor_columns_to_users_table.php'),
        ], 'filament-user-profile-migrations');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/filament-user-profile.php' => config_path('filament-user-profile.php'),
        ], 'filament-user-profile-config');

        // Check for 2FA columns early (this will log if missing)
        // Only check if Fortify 2FA is enabled
        if (Features::enabled(Features::twoFactorAuthentication())) {
            UserProfileHelper::hasTwoFactorColumns();
        }

        // User profile pages are registered via UserProfilePanelProvider
        // This creates a separate panel at /settings without tenancy

        // Listen for OAuth callbacks to handle account deletion
        // Listen to both Login (existing user) and SocialiteUserConnected (new connection) events
        // Use a very high priority to ensure we run first and can modify the redirect
        $handleDeletion = function ($event) {
            $eventType = get_class($event);
            \Illuminate\Support\Facades\Log::info("=== OAuth event fired: {$eventType} ===", [
                'provider' => $event->provider ?? null,
                'has_socialite_user' => isset($event->socialiteUser),
            ]);

            // Check if there's a deletion intent in the session
            $intent = Session::get('delete_account_intent');

            \Illuminate\Support\Facades\Log::info('Checking deletion intent', [
                'has_intent' => !is_null($intent),
                'intent_user_id' => $intent['user_id'] ?? null,
                'session_id' => Session::getId(),
            ]);

            if ($intent) {
                // Both Login and SocialiteUserConnected events have socialiteUser property
                $user = $event->socialiteUser->getUser();

                \Illuminate\Support\Facades\Log::info('User found in event', [
                    'user_id' => $user->id,
                    'intent_user_id' => $intent['user_id'],
                    'matches' => $intent['user_id'] === $user->id,
                    'expired' => now()->isAfter($intent['expires_at']),
                ]);

                // Verify deletion intent is valid
                if ($intent['user_id'] === $user->id && !now()->isAfter($intent['expires_at'])) {
                    // Store user ID and email before deletion (for logging)
                    $userId = $user->id;
                    $userEmail = $user->email;

                    \Illuminate\Support\Facades\Log::info('=== DELETING USER ===', [
                        'user_id' => $userId,
                        'user_email' => $userEmail,
                    ]);

                    // All checks passed - delete user immediately
                    $user->delete();

                    // Clear deletion intent
                    Session::forget('delete_account_intent');

                    // Set flag - DO NOT invalidate session here, let middleware handle it
                    // This ensures the flag persists for the middleware to catch
                    Session::put('account_deleted_after_oauth', true);
                    Session::put('deleted_user_email', $userEmail);
                    Session::save();

                    \Illuminate\Support\Facades\Log::info('Flag set in session', [
                        'flag_exists' => Session::has('account_deleted_after_oauth'),
                        'session_id' => Session::getId(),
                    ]);

                    // Just logout, don't invalidate session yet
                    Auth::guard('web')->logout();

                    \Illuminate\Support\Facades\Log::info('OAuth callback - user deleted, flag set, logged out', [
                        'user_id' => $userId,
                        'user_email' => $userEmail,
                    ]);
                } else {
                    // Invalid or expired intent
                    Session::forget('delete_account_intent');
                    \Illuminate\Support\Facades\Log::warning('Invalid or expired deletion intent');
                }
            } else {
                \Illuminate\Support\Facades\Log::info('No deletion intent found');
            }
        };

        // Listen to both events
        Event::listen(Login::class, $handleDeletion, 999);
        Event::listen(SocialiteUserConnected::class, $handleDeletion, 999);

        \Illuminate\Support\Facades\Log::info('FilamentUserProfileServiceProvider: Event listeners registered for Login and SocialiteUserConnected');
    }

    public function register(): void
    {
        // Register facade
        $this->app->singleton('filament-user-profile', function ($app) {
            return new UserProfileHelper;
        });

        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-user-profile.php', 'filament-user-profile');

        // Register the user profile panel provider
        $this->app->register(UserProfilePanelProvider::class);
    }
}
