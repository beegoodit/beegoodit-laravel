<?php

namespace BeegoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class Password extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $current_password = '';

    public ?string $password = '';

    public ?string $password_confirmation = '';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament-user-profile::pages.password';

    protected static ?string $title = 'Password';

    protected static ?string $navigationLabel = 'Password';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('filament-user-profile::messages.Update Password');
    }

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
        $panel ??= 'me';

        return parent::getUrl($parameters, $isAbsolute, $panel, null);
    }

    public function getHeading(): string
    {
        return __('filament-user-profile::messages.Update Password');
    }

    public function getSubheading(): ?string
    {
        return __('filament-user-profile::messages.Ensure your account is using a strong password');
    }

    public function mount(): void
    {
        $this->form->fill([
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-user-profile::messages.Update Password'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('filament-user-profile::messages.Current Password'))
                            ->password()
                            ->required()
                            ->autocomplete('current-password')
                            ->rules(['current_password']),

                        TextInput::make('password')
                            ->label(__('filament-user-profile::messages.New Password'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->autocomplete('new-password')
                            ->rules([PasswordRule::defaults(), 'confirmed']),

                        TextInput::make('password_confirmation')
                            ->label(__('filament-user-profile::messages.Confirm Password'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->autocomplete('new-password'),
                    ]),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Auth::user()->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->form->fill([]);

        Notification::make()
            ->success()
            ->title(__('filament-user-profile::messages.Password updated'))
            ->body(__('filament-user-profile::messages.Your password has been updated successfully.'))
            ->send();
    }
}
