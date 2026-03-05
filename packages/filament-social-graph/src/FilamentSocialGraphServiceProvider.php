<?php

namespace BeegoodIT\FilamentSocialGraph;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedItemCard;
use BeegoodIT\FilamentSocialGraph\Livewire\FeedList;
use BeegoodIT\FilamentSocialGraph\Livewire\SubscribeButton;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Policies\FeedItemPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class FilamentSocialGraphServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(FeedItem::class, FeedItemPolicy::class);

        $this->app->booted(function (): void {
            $this->registerLivewireComponents();
        });

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'filament-social-graph');

        $publishedPath = lang_path('vendor/filament-social-graph');
        if (file_exists($publishedPath)) {
            $this->loadTranslationsFrom($publishedPath, 'filament-social-graph');
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-social-graph');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-social-graph.php' => config_path('filament-social-graph.php'),
            ], 'filament-social-graph-config');

            $this->publishes([
                __DIR__.'/../database/migrations/2026_02_27_000000_create_feed_items_table.php' => database_path('migrations/2026_02_27_000000_create_feed_items_table.php'),
                __DIR__.'/../database/migrations/2026_02_27_000001_create_subscriptions_table.php' => database_path('migrations/2026_02_27_000001_create_subscriptions_table.php'),
            ], 'filament-social-graph-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-social-graph'),
            ], 'social-graph-views');

            $this->publishes([
                __DIR__.'/../database/migrations/add_team_id_to_social_graph_tables.php.stub' => database_path('migrations/2026_02_27_000010_add_team_id_to_social_graph_tables.php'),
            ], 'social-graph-team-migration');

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/filament-social-graph'),
            ], 'filament-social-graph-translations');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-social-graph.php', 'filament-social-graph');
    }

    protected function registerLivewireComponents(): void
    {
        $components = [
            FeedItemCard::class,
            FeedList::class,
            SubscribeButton::class,
        ];

        foreach ($components as $class) {
            $name = $this->livewireNameForClass($class);
            Livewire::component($name, $class);
        }
    }

    /**
     * Generate the same name Livewire uses for a class (kebab-case segments).
     */
    protected function livewireNameForClass(string $class): string
    {
        $parts = explode('\\', trim($class, '\\'));

        return collect($parts)->map(fn (string $segment): string => Str::kebab($segment))->implode('.');
    }
}
