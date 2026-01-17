<?php

namespace Beegoodit\FilamentConnect;

use Illuminate\Support\ServiceProvider;

class FilamentConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-connect.php', 'filament-connect');

        $this->app->singleton(Connect::class, fn() => new Connect());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-connect.php' => config_path('filament-connect.php'),
            ], 'connect-config');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-connect');

        \Livewire\Livewire::component('beegoodit.filament-connect.filament.relation-managers.connect-relation-manager', \Beegoodit\FilamentConnect\Filament\RelationManagers\ConnectRelationManager::class);
        \Livewire\Livewire::component(\Beegoodit\FilamentConnect\Filament\RelationManagers\ConnectRelationManager::class, \Beegoodit\FilamentConnect\Filament\RelationManagers\ConnectRelationManager::class);
    }
}
