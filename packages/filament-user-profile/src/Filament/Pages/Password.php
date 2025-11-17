<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;

class Password extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament-user-profile::pages.password';

    protected static ?string $title = 'Password';

    protected static ?string $navigationLabel = 'Password';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'password';
    }

    public function getHeading(): string
    {
        return __('Update Password');
    }

    public function getSubheading(): ?string
    {
        return __('Ensure your account is using a strong password');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Placeholder - will be implemented in Phase 3
            ]);
    }

    public function submit(): void
    {
        // Placeholder - will be implemented in Phase 3
    }
}

