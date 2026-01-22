<?php

namespace BeegoodIT\FilamentI18n;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FilamentI18nServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-i18n.php', 'filament-i18n');

        // Register facade singleton
        $this->app->singleton('filament-i18n', fn($app) => new I18nHelper);
    }

    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-i18n');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-i18n');

        // Register Blade components
        Blade::componentNamespace('BeegoodIT\\FilamentI18n\\View\\Components', 'filament-i18n');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/filament-i18n.php' => config_path('filament-i18n.php'),
        ], 'filament-i18n-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/add_i18n_fields.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_add_i18n_fields_to_users_table.php'),
        ], 'filament-i18n-migrations');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-i18n'),
        ], 'filament-i18n-lang');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-i18n'),
        ], 'filament-i18n-views');
    }
}
