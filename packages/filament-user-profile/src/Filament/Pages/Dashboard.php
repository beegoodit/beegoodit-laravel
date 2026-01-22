<?php

namespace BeegoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Pages\Page;
use Filament\Facades\Filament;

class Dashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament-user-profile::pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -10;

    public static function getNavigationLabel(): string
    {
        return __('filament-user-profile::messages.Dashboard');
    }

    public function getTitle(): string
    {
        return __('filament-user-profile::messages.Dashboard');
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return '/';
    }
}
