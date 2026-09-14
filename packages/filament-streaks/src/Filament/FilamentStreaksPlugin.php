<?php

namespace BeegoodIT\FilamentStreaks\Filament;

use BeegoodIT\FilamentStreaks\Filament\Clusters\Cluster;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource;
use BeegoodIT\FilamentStreaks\Filament\Resources\NotificationResource;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource;
use BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentStreaksPlugin implements Plugin
{
    public function getId(): string
    {
        return 'beegoodit-filament-streaks';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                Cluster::class,
            ])
            ->resources([
                StreakResource::class,
                QualificationResource::class,
                NotificationResource::class,
                HistoryResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
