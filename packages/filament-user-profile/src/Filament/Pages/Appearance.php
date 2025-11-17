<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;

class Appearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paint-brush';

    protected string $view = 'filament-user-profile::pages.appearance';

    protected static ?string $title = 'Appearance';

    protected static ?string $navigationLabel = 'Appearance';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'appearance';
    }

    public function getHeading(): string
    {
        return __('Appearance Settings');
    }

    public function getSubheading(): ?string
    {
        return __('Customize your interface appearance');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Placeholder - will be implemented in Phase 4
            ]);
    }

    public function submit(): void
    {
        // Placeholder - will be implemented in Phase 4
    }
}

