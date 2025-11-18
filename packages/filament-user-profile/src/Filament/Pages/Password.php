<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Facades\Filament;
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

    // Navigation is enabled for the settings panel

    // This page is in a non-tenant panel, so isTenanted() is not needed

    public static function getSlug(?Panel $panel = null): string
    {
        return 'password';
    }

    // Routes are registered by the UserProfilePanelProvider

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        // Use the user-profile panel (no tenant)
        $panel = $panel ?? 'user-profile';
        return parent::getUrl($parameters, $isAbsolute, $panel, null);
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

