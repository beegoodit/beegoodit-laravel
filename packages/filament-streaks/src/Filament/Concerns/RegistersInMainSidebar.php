<?php

namespace BeegoodIT\FilamentStreaks\Filament\Concerns;

use Filament\Facades\Filament;

trait RegistersInMainSidebar
{
    public static function registerNavigationItems(): void
    {
        if (! static::shouldRegisterNavigation()) {
            return;
        }

        if (! static::canAccess()) {
            return;
        }

        Filament::getCurrentOrDefaultPanel()
            ->navigationItems(static::getNavigationItems());
    }
}
