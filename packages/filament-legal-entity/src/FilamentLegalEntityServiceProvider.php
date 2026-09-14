<?php

namespace BeegoodIT\FilamentLegalEntity;

use Illuminate\Support\ServiceProvider;

class FilamentLegalEntityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(FilamentLegalEntityPlugin::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-legal-entity');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-legal-entity-migrations');

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/filament-legal-entity'),
            ], 'filament-legal-entity-translations');
        }
    }
}
