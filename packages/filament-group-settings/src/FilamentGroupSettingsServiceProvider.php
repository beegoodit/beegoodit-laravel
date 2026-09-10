<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings;

use BeegoodIT\FilamentGroupSettings\Console\MakeGroupSettingsPageCommand;
use Illuminate\Support\ServiceProvider;

class FilamentGroupSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-group-settings');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-group-settings');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeGroupSettingsPageCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/filament-group-settings'),
            ], 'filament-group-settings-translations');
        }
    }
}
