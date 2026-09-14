<?php

namespace BeegoodIT\FilamentStreaks;

use BeegoodIT\FilamentStreaks\Console\Commands\AtRiskStreaksCommand;
use BeegoodIT\FilamentStreaks\Console\Commands\EvaluateStreaksCommand;
use BeegoodIT\FilamentStreaks\Console\Commands\HistoryStreaksCommand;
use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Policies\NotificationPolicy;
use BeegoodIT\FilamentStreaks\Policies\OperationRunPolicy;
use BeegoodIT\FilamentStreaks\Policies\QualificationPolicy;
use BeegoodIT\FilamentStreaks\Policies\StreakPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FilamentStreaksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-streaks.php', 'filament-streaks');
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-streaks');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-streaks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Gate::policy(Streak::class, StreakPolicy::class);
        Gate::policy(Qualification::class, QualificationPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(OperationRun::class, OperationRunPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                EvaluateStreaksCommand::class,
                AtRiskStreaksCommand::class,
                HistoryStreaksCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/filament-streaks.php' => config_path('filament-streaks.php'),
            ], 'filament-streaks-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-streaks-migrations');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/filament-streaks'),
            ], 'filament-streaks-lang');
        }
    }
}
