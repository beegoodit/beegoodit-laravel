<?php

namespace BeegoodIT\LaravelPwa\Filament\Pages;

use BeegoodIT\LaravelPwa\Settings\NotificationSettings;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class ManageNotificationSettings extends SettingsPage
{
    protected static string $settings = NotificationSettings::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationGroup(): ?string
    {
        return __('laravel-pwa::notifications.nav.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.settings.title');
    }

    public function getTitle(): string
    {
        return __('laravel-pwa::notifications.settings.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('pwa_deliver_notifications')
                    ->label(__('laravel-pwa::notifications.settings.fields.pwa_deliver_notifications.label'))
                    ->helperText(__('laravel-pwa::notifications.settings.fields.pwa_deliver_notifications.description'))
                    ->default(true),
            ]);
    }
}
