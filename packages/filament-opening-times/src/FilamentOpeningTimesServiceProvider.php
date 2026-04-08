<?php

namespace BeegoodIT\FilamentOpeningTimes;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Policies\SchedulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FilamentOpeningTimesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-opening-times.php', 'filament-opening-times');
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-opening-times');

        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-opening-times-migrations');

            $this->publishes([
                __DIR__.'/../config/filament-opening-times.php' => config_path('filament-opening-times.php'),
            ], 'filament-opening-times-config');

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/filament-opening-times'),
            ], 'filament-opening-times-lang');
        }

        Gate::policy(Schedule::class, SchedulePolicy::class);
    }
}
