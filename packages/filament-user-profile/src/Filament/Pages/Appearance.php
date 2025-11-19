<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use BeeGoodIT\FilamentUserProfile\Filament\Forms\Components\TimezonePicker;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class Appearance extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $timezone = null;

    public ?string $locale = null;

    public ?string $time_format = null;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paint-brush';

    protected string $view = 'filament-user-profile::pages.appearance';

    protected static ?string $title = 'Appearance';

    protected static ?string $navigationLabel = 'Appearance';

    protected static ?int $navigationSort = 3;

    // Navigation is enabled for the settings panel

    // This page is in a non-tenant panel, so isTenanted() is not needed

    public static function getSlug(?Panel $panel = null): string
    {
        return 'appearance';
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
        return __('Appearance Settings');
    }

    public function getSubheading(): ?string
    {
        return __('Customize your interface appearance and localization preferences');
    }

    public function mount(): void
    {
        $user = Auth::user();

        // Set public properties that Filament Forms will automatically bind to
        $this->locale = $user->locale ?? config('app.locale', 'en');
        $this->timezone = $user->timezone ?? config('app.timezone', 'UTC');
        $this->time_format = $user->time_format ?? '24h';

        // Also fill the form to ensure state is properly initialized
        $this->form->fill([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'time_format' => $this->time_format,
        ]);
    }

    public function updatedTimezone($value): void
    {
        // Merge timezone with existing form state instead of replacing it
        $currentState = $this->form->getState();
        $mergedState = array_merge($currentState, ['timezone' => $value]);
        $this->form->fill($mergedState);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('locale')
                    ->label(__('Language'))
                    ->options([
                        'en' => __('English'),
                        'de' => __('Deutsch'),
                    ])
                    ->inline()
                    ->required(),

                TimezonePicker::make('timezone')
                    ->label(__('Timezone'))
                    ->required(),

                Radio::make('time_format')
                    ->label(__('Time Format'))
                    ->options([
                        '12h' => __('12-hour (3:45 PM)'),
                        '24h' => __('24-hour (15:45)'),
                    ])
                    ->inline()
                    ->required(),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        if (! $user instanceof Authenticatable) {
            return;
        }

        $user->update([
            'locale' => $data['locale'],
            'timezone' => $data['timezone'],
            'time_format' => $data['time_format'],
        ]);

        // Update app locale for current session
        app()->setLocale($data['locale']);

        Session::flash('status', 'appearance-updated');

        $this->dispatch('appearance-updated');
    }
}

