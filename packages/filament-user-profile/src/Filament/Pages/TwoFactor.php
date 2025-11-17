<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;
use Laravel\Fortify\Features;
use Symfony\Component\HttpFoundation\Response;

class TwoFactor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament-user-profile::pages.two-factor';

    protected static ?string $title = 'Two-Factor Authentication';

    protected static ?string $navigationLabel = 'Two-Factor Authentication';

    protected static ?int $navigationSort = 4;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'two-factor';
    }

    public function getHeading(): string
    {
        return __('Two-Factor Authentication');
    }

    public function getSubheading(): ?string
    {
        return __('Add additional security to your account');
    }

    public function mount(): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Placeholder - will be implemented in Phase 5
            ]);
    }

    public function submit(): void
    {
        // Placeholder - will be implemented in Phase 5
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hidden from navigation, accessible via user menu only
    }
}

