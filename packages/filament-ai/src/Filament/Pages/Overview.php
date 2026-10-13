<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Overview extends Page
{
    protected static ?int $navigationSort = -5;

    protected string $view = 'filament-ai::pages.overview';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return config('filament-ai.navigation_icon', 'heroicon-o-sparkles');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-ai::ai.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-ai::ai.navigation.overview');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.overview.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.overview.heading');
    }
}
