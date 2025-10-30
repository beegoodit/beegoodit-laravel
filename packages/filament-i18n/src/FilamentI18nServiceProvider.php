<?php

namespace BeeGoodIT\FilamentI18n;

use Illuminate\Support\ServiceProvider;

class FilamentI18nServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-i18n');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/add_i18n_fields.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_i18n_fields_to_users_table.php'),
        ], 'filament-i18n-migrations');

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/filament-i18n'),
        ], 'filament-i18n-lang');
    }
}

