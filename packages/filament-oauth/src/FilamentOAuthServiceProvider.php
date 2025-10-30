<?php

namespace BeeGoodIT\FilamentOAuth;

use BeeGoodIT\FilamentOAuth\Listeners\AssignUserToTeamAfterOAuthConnection;
use BeeGoodIT\FilamentOAuth\Listeners\AssignUserToTeamAfterOAuthRegistration;
use DutchCodingCompany\FilamentSocialite\Events\SocialiteUserConnected;
use DutchCodingCompany\FilamentSocialite\Events\SocialiteUserRegistered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FilamentOAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\BeeGoodIT\FilamentOAuth\Services\TeamAssignmentService::class);
    }

    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/create_oauth_accounts_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_oauth_accounts_table.php'),
        ], 'oauth-migrations');

        // Register event listeners
        Event::listen(SocialiteUserRegistered::class, AssignUserToTeamAfterOAuthRegistration::class);
        Event::listen(SocialiteUserConnected::class, AssignUserToTeamAfterOAuthConnection::class);
    }
}

