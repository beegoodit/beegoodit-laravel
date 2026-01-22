<?php

namespace BeegoodIT\FilamentLegal;

use Illuminate\Support\ServiceProvider;

class FilamentLegalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-legal');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'filament-legal');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'filament-legal-migrations');

            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/filament-legal'),
            ], 'filament-legal-translations');
        }
    }
}
