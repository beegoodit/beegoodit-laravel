<?php

namespace BeegoodIT\FilamentSocialGraph\Filament;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\SubscriptionResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentSocialGraphPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-social-graph';
    }

    public function register(Panel $panel): void
    {
        if (! config('filament-social-graph.resources.enabled', true)) {
            return;
        }

        $panel->resources([
            FeedItemResource::class,
            SubscriptionResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return resolve(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin('filament-social-graph');

        return $plugin;
    }
}
