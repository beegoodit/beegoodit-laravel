<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Pages;

use BackedEnum;
use BeegoodIT\FilamentGroupSettings\GroupSettingsPlugin;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class SettingsLanding extends Page
{
    protected static bool $isDiscovered = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'settings';

    protected string $view = 'filament-group-settings::pages.landing';

    public static function getNavigationLabel(): string
    {
        return static::pluginLandingLabel()
            ?? __('filament-group-settings::messages.Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return GroupSettingsPlugin::get()->hasLanding();
        } catch (Throwable) {
            return false;
        }
    }

    public static function canAccess(): bool
    {
        if (! auth()->check() || Filament::getTenant() === null) {
            return false;
        }

        return static::shouldRegisterNavigation();
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public function getLinkedPages(): array
    {
        try {
            $plugin = GroupSettingsPlugin::get();
        } catch (Throwable) {
            return [];
        }

        $links = [];

        foreach ($plugin->getPages() as $page) {
            if (! $page::canAccess()) {
                continue;
            }

            $group = $page::getNavigationGroup();
            $label = is_string($group) && $group !== ''
                ? $group
                : $page::getNavigationLabel();

            $links[] = [
                'label' => $label,
                'url' => $page::getUrl(),
            ];
        }

        return $links;
    }

    protected static function pluginLandingLabel(): ?string
    {
        try {
            return GroupSettingsPlugin::get()->getLandingNavigationLabel();
        } catch (Throwable) {
            return null;
        }
    }
}
