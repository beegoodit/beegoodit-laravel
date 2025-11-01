<?php

namespace BeeGoodIT\FilamentOAuth;

use BeeGoodIT\FilamentOAuth\Services\TeamAssignmentService;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use DutchCodingCompany\FilamentSocialite\Events\SocialiteUserConnected;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FilamentOAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TeamAssignmentService::class);

        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-oauth.php',
            'filament-oauth'
        );
    }

    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/create_oauth_accounts_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_oauth_accounts_table.php'),
        ], 'oauth-migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/filament-oauth.php' => config_path('filament-oauth.php'),
        ], 'filament-oauth-config');

        // Register Microsoft Socialite driver
        // This must happen early, so we use Event::listen for the SocialiteWasCalled event
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class, 'handle']
        );

        // Optional team assignment listeners (configurable)
        if (config('filament-oauth.auto_assign_teams', true)) {
            $this->registerTeamAssignmentListeners();
        }

        // Show helpful message when migrations are published
        if ($this->app->runningInConsole()) {
            // Display migration instructions
            if (isset($_SERVER['argv']) && in_array('vendor:publish', $_SERVER['argv'])) {
                $this->showMigrationInstructions();
            }
        }
    }

    /**
     * Register event listeners for automatic team assignment.
     */
    protected function registerTeamAssignmentListeners(): void
    {
        $teamAssignmentService = $this->app->make(TeamAssignmentService::class);

        // Handle new user registration via OAuth
        Event::listen(Registered::class, function ($event) use ($teamAssignmentService) {
            $user = $event->getUser();
            $socialiteUser = $event->socialiteUser;
            $provider = $event->provider;

            // Extract tenant ID from OAuth data (Microsoft specific)
            if ($provider === 'microsoft') {
                $tenantId = $this->extractMicrosoftTenantId($socialiteUser);
                
                if ($tenantId) {
                    $teamAssignmentService->assignUserToTeam($user, $provider, $tenantId);
                }
            }
        });

        // Handle existing user connecting OAuth account
        Event::listen(SocialiteUserConnected::class, function ($event) use ($teamAssignmentService) {
            $user = $event->getUser();
            $socialiteUser = $event->socialiteUser;
            $provider = $event->provider;

            // Extract tenant ID from OAuth data (Microsoft specific)
            if ($provider === 'microsoft') {
                $tenantId = $this->extractMicrosoftTenantId($socialiteUser);
                
                if ($tenantId) {
                    $teamAssignmentService->assignUserToTeam($user, $provider, $tenantId);
                }
            }
        });
    }

    /**
     * Extract tenant ID from Microsoft OAuth user data.
     */
    protected function extractMicrosoftTenantId($oauthUser): ?string
    {
        // Try to get from user data
        $tenantId = $oauthUser->user['tid'] ?? null;

        // If not in user data, try access token response
        if (! $tenantId && isset($oauthUser->accessTokenResponseBody)) {
            $tokenData = $oauthUser->accessTokenResponseBody;
            $tenantId = $tokenData['tenant_id'] ?? $tokenData['tid'] ?? null;
        }

        // If still no tenant ID, try to decode the JWT token
        if (! $tenantId && isset($oauthUser->token)) {
            try {
                $tokenParts = explode('.', $oauthUser->token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    $tenantId = $payload['tid'] ?? $payload['tenant_id'] ?? null;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to decode JWT token for tenant ID extraction', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $tenantId;
    }

    /**
     * Show migration instructions to users.
     */
    protected function showMigrationInstructions(): void
    {
        if (! config('filament-oauth.suppress_instructions', false)) {
            echo "\n\033[1;33m⚠️  Important:\033[0m FilamentOAuth requires filament-socialite migrations.\n";
            echo "\033[0;33mIf not already published, run:\033[0m\n";
            echo "  \033[1mphp artisan vendor:publish --tag=filament-socialite-migrations\033[0m\n\n";
        }
    }
}

