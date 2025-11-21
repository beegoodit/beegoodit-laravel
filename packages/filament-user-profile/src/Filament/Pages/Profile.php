<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Models\Contracts\HasAvatar;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class Profile extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    public ?string $name = null;

    public ?string $email = null;

    public $avatarUpload = null;

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
        $user = Auth::user();

        // Validate and update profile information
        $validated = $this->form->getState();

        $user->name = $validated['name'];
        
        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            if ($user instanceof MustVerifyEmail) {
                $user->email_verified_at = null;
            }
        }

        $user->save();

        Session::flash('status', 'profile-updated');
    }

    /**
     * Check if the current user model supports avatar uploads.
     */
    protected function supportsAvatar(): bool
    {
        $user = Auth::user();
        return $user instanceof HasAvatar && method_exists($user, 'getAvatarUrl');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(): void
    {
        $user = Auth::user();

        if (!$this->supportsAvatar() || !$this->avatarUpload) {
            return;
        }

        $this->validate([
            'avatarUpload' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        try {
            // Check if AvatarService is available (from filament-user-avatar package)
            if (class_exists(\BeeGoodIT\FilamentUserAvatar\Services\AvatarService::class)) {
                $avatarService = app(\BeeGoodIT\FilamentUserAvatar\Services\AvatarService::class);
                
                // Get file content and extension
                $fileContent = file_get_contents($this->avatarUpload->getRealPath());
                $extension = $this->avatarUpload->getClientOriginalExtension();
                
                // Store avatar
                $avatarPath = $avatarService->storeAvatar($user, $fileContent, $extension);
                
                // Update user record and delete old avatar
                $avatarService->updateUserAvatar($user, $avatarPath);
            } else {
                // Fallback: handle avatar upload directly
                $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
                $path = $this->avatarUpload->store('users/' . $user->id . '/avatar', $disk);
                
                // Delete old avatar if exists
                if ($user->avatar) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->delete($user->avatar);
                }
                
                // Update user
                $user->avatar = $path;
                $user->save();
            }

            $this->reset('avatarUpload');
            Session::flash('status', 'avatar-updated');
        } catch (\Exception $e) {
            Session::flash('status', 'avatar-update-failed');
            \Illuminate\Support\Facades\Log::error('Avatar update failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if (!$this->supportsAvatar()) {
            return;
        }

        try {
            // Check if AvatarService is available
            if (class_exists(\BeeGoodIT\FilamentUserAvatar\Services\AvatarService::class)) {
                $avatarService = app(\BeeGoodIT\FilamentUserAvatar\Services\AvatarService::class);
                $avatarService->deleteAvatar($user);
            } else {
                // Fallback: delete avatar directly
                $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
                if ($user->avatar) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->delete($user->avatar);
                }
            }

            $user->avatar = null;
            $user->save();

            Session::flash('status', 'avatar-removed');
        } catch (\Exception $e) {
            Session::flash('status', 'avatar-remove-failed');
            \Illuminate\Support\Facades\Log::error('Avatar removal failed: ' . $e->getMessage());
        }
    }
}

