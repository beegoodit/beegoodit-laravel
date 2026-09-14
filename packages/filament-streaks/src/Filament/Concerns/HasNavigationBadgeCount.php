<?php

namespace BeegoodIT\FilamentStreaks\Filament\Concerns;

trait HasNavigationBadgeCount
{
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}
