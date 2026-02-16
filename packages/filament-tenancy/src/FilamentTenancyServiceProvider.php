<?php

namespace BeegoodIT\FilamentTenancy;

use Illuminate\Support\ServiceProvider;

class FilamentTenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-tenancy');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \BeegoodIT\FilamentTenancy\Console\Commands\SeedDemoTeamCommand::class,
            ]);
        }

        // Publish migrations (order: create_teams → create_team_user → add_team_branding)
        $t = time();
        $this->publishes([
            __DIR__.'/../database/migrations/create_teams_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', $t).'_create_teams_table.php'),
            __DIR__.'/../database/migrations/create_team_user_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', $t + 1).'_create_team_user_table.php'),
            __DIR__.'/../database/migrations/add_team_branding.php.stub' => database_path('migrations/'.date('Y_m_d_His', $t + 2).'_add_team_branding.php'),
        ], 'tenancy-migrations');
    }
}
