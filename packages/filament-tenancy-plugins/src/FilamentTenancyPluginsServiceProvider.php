<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTenancyPlugins;

use BeegoodIT\FilamentTenancyPlugins\Console\AssignCommand;
use BeegoodIT\FilamentTenancyPlugins\Console\ListCommand;
use BeegoodIT\FilamentTenancyPlugins\Console\RevokeCommand;
use Illuminate\Support\ServiceProvider;

class FilamentTenancyPluginsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-tenancy-plugins.php',
            'filament-tenancy-plugins',
        );

        $this->app->singleton(Tenant::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListCommand::class,
                AssignCommand::class,
                RevokeCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/filament-tenancy-plugins.php' => config_path('filament-tenancy-plugins.php'),
            ], 'filament-tenancy-plugins-config');
        }
    }
}
