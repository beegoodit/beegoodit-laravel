<?php

namespace BeegoodIT\FilamentSocialGraph;

use BeegoodIT\FilamentSocialGraph\Models\FeedItemAttachment;
use BeegoodIT\FilamentSocialGraph\Models\Observers\FeedItemAttachmentObserver;
use Illuminate\Support\ServiceProvider;

class FilamentSocialGraphServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FeedItemAttachment::observe(FeedItemAttachmentObserver::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-social-graph');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-social-graph');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-social-graph.php' => config_path('filament-social-graph.php'),
            ], 'filament-social-graph-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-social-graph'),
            ], 'social-graph-views');

            $this->publishes([
                __DIR__.'/../database/migrations/add_team_id_to_social_graph_tables.php.stub' => database_path('migrations/2026_02_27_000010_add_team_id_to_social_graph_tables.php'),
            ], 'social-graph-team-migration');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-social-graph.php', 'filament-social-graph');
    }
}
