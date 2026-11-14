<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Overview extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-ai::pages.overview';

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.overview.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.overview.heading');
    }
}
