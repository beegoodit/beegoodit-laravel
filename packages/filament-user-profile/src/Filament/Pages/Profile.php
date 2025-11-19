<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $name = null;

    public ?string $email = null;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament-user-profile::pages.profile';

    protected static ?string $title = 'Profile';

    protected static ?string $navigationLabel = 'Profile';

    protected static ?int $navigationSort = 1;

    // Navigation is enabled for the settings panel

    // This page is in a non-tenant panel, so isTenanted() is not needed

    public static function getSlug(?Panel $panel = null): string
    {
        return 'profile';
    }

    // Routes are registered by the UserProfilePanelProvider

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        // Use the user-profile panel (no tenant)
        $panel = $panel ?? 'user-profile';
        return parent::getUrl($parameters, $isAbsolute, $panel, null);
    }

    public function mount(): void
    {
        $user = Auth::user();

        // Set public properties that Filament Forms will automatically bind to
        $this->name = $user->name;
        $this->email = $user->email;

        // Also fill the form to ensure state is properly initialized
        $this->form->fill([
            'name' => $this->name,
            'email' => $this->email,
        ]);
    }

    public function getHeading(): string
    {
        return __('Profile');
    }

    public function getSubheading(): ?string
    {
        return __('Update your name and email address');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->autocomplete('name'),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('email')
                            ->unique(
                                table: config('auth.providers.users.model'),
                                column: 'email',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->ignore(Auth::id()),
                            )
                            ->helperText(function () {
                                $user = Auth::user();
                                if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                                    return __('Your email address is unverified.');
                                }
                                return null;
                            }),
                    ]),
            ]);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function submit(): void
    {
        // Placeholder - will be implemented in Phase 2
    }
}

