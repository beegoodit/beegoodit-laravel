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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class Profile extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    public ?string $name = null;

    public ?string $email = null;

    public $avatarUpload = null;

    public string $deletePassword = '';

    public bool $showDeleteModal = false;

    public bool $confirmDelete = false;

    /**
     * Computed property to check if user has OAuth accounts.
     * This is used in the view to determine which UI to show.
     */
    public function hasOAuthAccounts(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Try using the relationship first
        if (method_exists($user, 'oauthAccounts')) {
            try {
                return $user->oauthAccounts()->exists();
            } catch (\Exception $e) {
                // Fall through to DB query
            }
        }

        // Fallback: Query oauth_accounts table directly
        if (\Illuminate\Support\Facades\Schema::hasTable('oauth_accounts')) {
            try {
                if (DB::table('oauth_accounts')->where('user_id', $user->id)->exists()) {
                    return true;
                }
            } catch (\Exception $e) {
                // Continue to check socialite_users
            }
        }

        // Also check socialite_users table (used by FilamentSocialite)
        if (\Illuminate\Support\Facades\Schema::hasTable('socialite_users')) {
            try {
                return DB::table('socialite_users')
                    ->where('user_id', $user->id)
                    ->exists();
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament-user-profile::pages.profile';

    protected static ?string $title = 'Profile';

    protected static ?string $navigationLabel = 'Profile';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('filament-user-profile::messages.Profile');
    }

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

        $panelInstance = Filament::getPanel($panel);

        if ($panelInstance) {
            // Construct URL using panel path and page slug
            $url = $panelInstance->getPath().'/'.static::getSlug($panelInstance);

            if ($isAbsolute) {
                return url($url);
            }

            return '/'.ltrim($url, '/');
        }

        // Fallback to parent method
        return parent::getUrl($parameters, $isAbsolute, $panel, null);
    }

    public function mount(): void
    {
        $user = Auth::user();

        // Check if we need to complete account deletion after OAuth callback
        if (Session::get('complete_delete_after_oauth') && Session::has('delete_account_intent')) {
            Session::forget('complete_delete_after_oauth');
            $this->completeDeleteAccount();

            return;
        }

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
        return __('filament-user-profile::messages.Profile');
    }

    public function getSubheading(): ?string
    {
        return __('filament-user-profile::messages.Update your name and email address');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-user-profile::messages.Profile Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament-user-profile::messages.Name'))
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->autocomplete('name'),

                        TextInput::make('email')
                            ->label(__('filament-user-profile::messages.Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('email')
                            ->rules(['lowercase'])
                            ->unique(
                                table: config('auth.providers.users.model'),
                                column: 'email',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->ignore(Auth::id()),
                            )
                            ->helperText(function () {
                                $user = Auth::user();
                                if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                                    return __('filament-user-profile::messages.Your email address is unverified.');
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

        // Lowercase email to match eveant's behavior
        $email = strtolower($validated['email']);

        if ($user->email !== $email) {
            $user->email = $email;
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

        if (! $this->supportsAvatar() || ! $this->avatarUpload) {
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
                $path = $this->avatarUpload->store('users/'.$user->id.'/avatar', $disk);

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
            \Illuminate\Support\Facades\Log::error('Avatar update failed: '.$e->getMessage());
        }
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if (! $this->supportsAvatar()) {
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
            \Illuminate\Support\Facades\Log::error('Avatar removal failed: '.$e->getMessage());
        }
    }

    /**
     * Open the delete user account modal.
     */
    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
        $this->deletePassword = '';
    }

    /**
     * Close the delete user account modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletePassword = '';
    }

    /**
     * Check if the current user is OAuth-only (has no password).
     */
    protected function isOAuthOnlyUser(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Check if user model uses HasOAuth trait
        if (method_exists($user, 'isOAuthOnly')) {
            return $user->isOAuthOnly();
        }

        // Fallback: check if password is null
        return is_null($user->password);
    }

    /**
     * Get the primary OAuth provider for the user.
     */
    protected function getOAuthProvider(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Refresh user to ensure we have latest data
        $user->refresh();

        // First, try using the relationship directly (most reliable)
        if (method_exists($user, 'oauthAccounts')) {
            try {
                // Check if relationship returns anything
                $account = $user->oauthAccounts()->first();

                if ($account) {
                    // Access provider property
                    $provider = $account->provider ?? null;
                    if ($provider) {
                        return $provider;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::debug('OAuth accounts relationship failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'user_class' => get_class($user),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Try using the trait's helper method if available
        if (method_exists($user, 'getOAuthAccount')) {
            // Try common providers in order
            $providers = ['microsoft', 'google', 'github', 'facebook'];
            foreach ($providers as $provider) {
                try {
                    $account = $user->getOAuthAccount($provider);
                    if ($account && isset($account->provider)) {
                        return $account->provider;
                    }
                } catch (\Exception $e) {
                    // Continue to next provider
                    continue;
                }
            }
        }

        // Fallback: Query oauth_accounts table directly
        // This handles cases where the relationship might not be set up
        if (\Illuminate\Support\Facades\Schema::hasTable('oauth_accounts')) {
            try {
                $userId = $user->id;
                $account = DB::table('oauth_accounts')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($account && isset($account->provider)) {
                    return $account->provider;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::debug('Direct OAuth accounts query failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also check socialite_users table (used by FilamentSocialite)
        if (\Illuminate\Support\Facades\Schema::hasTable('socialite_users')) {
            try {
                $userId = $user->id;
                $account = DB::table('socialite_users')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($account && isset($account->provider)) {
                    return $account->provider;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::debug('Direct socialite_users query failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Initiate account deletion for OAuth users.
     * Stores deletion intent and redirects to OAuth for re-authentication.
     */
    public function initiateDeleteAccount(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Not authenticated');
        }

        // Validate confirmation checkbox
        $this->validate([
            'confirmDelete' => 'accepted',
        ]);

        // Double-check: if user has a password, redirect to password deletion
        // This handles cases where the view incorrectly showed OAuth UI
        if (! is_null($user->password) && $user->password !== '') {
            throw ValidationException::withMessages([
                'confirmDelete' => __('filament-user-profile::messages.You have a password set. Please close this modal and use the password field to delete your account.'),
            ]);
        }

        // Get OAuth provider BEFORE storing deletion intent
        // This way we fail fast if provider can't be determined
        $provider = $this->getOAuthProvider();

        if (! $provider) {
            // Log detailed debugging information
            \Illuminate\Support\Facades\Log::error('Unable to determine OAuth provider for user deletion', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'has_password' => ! is_null($user->password),
                'password_is_null' => is_null($user->password),
                'password_is_empty' => $user->password === '',
                'has_oauth_accounts_method' => method_exists($user, 'oauthAccounts'),
                'has_getOAuthAccount_method' => method_exists($user, 'getOAuthAccount'),
                'oauth_accounts_count' => method_exists($user, 'oauthAccounts') ? $user->oauthAccounts()->count() : 0,
                'db_oauth_accounts_count' => DB::table('oauth_accounts')->where('user_id', $user->id)->count(),
                'db_oauth_accounts' => DB::table('oauth_accounts')->where('user_id', $user->id)->get()->toArray(),
            ]);

            throw ValidationException::withMessages([
                'confirmDelete' => __('filament-user-profile::messages.Unable to determine OAuth provider. Please ensure you have connected an OAuth account. If you have a password, please use the password field instead. Otherwise, please contact support.'),
            ]);
        }

        // Store deletion intent in session (10 minute window)
        Session::put('delete_account_intent', [
            'user_id' => $user->id,
            'timestamp' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        // Redirect to OAuth provider for re-authentication
        // Use the portal panel's OAuth redirect route (FilamentSocialite route naming)
        $this->redirect(route('socialite.filament.portal.oauth.redirect', ['provider' => $provider]));
    }

    /**
     * Complete account deletion after OAuth re-authentication.
     * This is called after the OAuth callback.
     */
    public function completeDeleteAccount(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Not authenticated');
        }

        // Verify deletion intent exists and is valid
        $intent = Session::get('delete_account_intent');

        if (! $intent) {
            Session::flash('error', __('filament-user-profile::messages.Deletion request expired or invalid.'));
            $this->redirect(static::getUrl());

            return;
        }

        // Check expiration
        if (now()->isAfter($intent['expires_at'])) {
            Session::forget('delete_account_intent');
            Session::flash('error', __('filament-user-profile::messages.Deletion request expired. Please try again.'));
            $this->redirect(static::getUrl());

            return;
        }

        // Verify user ID matches
        if ($intent['user_id'] !== $user->id) {
            Session::forget('delete_account_intent');
            abort(403, 'User identity mismatch');
        }

        // All checks passed - delete user
        $user->delete();

        // Clear deletion intent
        Session::forget('delete_account_intent');

        // Logout and redirect
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->redirect('/', navigate: false);
    }

    /**
     * Delete the user account (for password users only).
     */
    public function deleteUser(): void
    {
        // This method is only for password users
        if ($this->isOAuthOnlyUser()) {
            abort(403, 'OAuth users must use initiateDeleteAccount() method');
        }

        $this->validate([
            'deletePassword' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        if (! $user) {
            abort(403, 'Not authenticated');
        }

        // Verify user still authenticated
        if (Auth::id() !== $user->id) {
            abort(403, 'Authentication mismatch');
        }

        // Delete the user
        $user->delete();

        // Log out the user
        Auth::guard('web')->logout();

        // Invalidate session
        Session::invalidate();
        Session::regenerateToken();

        // Redirect to home page
        $this->redirect('/', navigate: false);
    }
}
