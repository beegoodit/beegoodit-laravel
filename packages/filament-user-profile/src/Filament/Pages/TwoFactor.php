<?php

namespace BeeGoodIT\FilamentUserProfile\Filament\Pages;

use BeeGoodIT\FilamentUserProfile\UserProfileHelper;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Symfony\Component\HttpFoundation\Response;

class TwoFactor extends Page implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public bool $twoFactorEnabled = false;

    #[Locked]
    public bool $requiresConfirmation = false;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public array $recoveryCodes = [];

    public bool $showRecoveryCodes = false;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament-user-profile::pages.two-factor';

    protected static ?string $title = 'Two-Factor Authentication';

    protected static ?string $navigationLabel = 'Two-Factor Authentication';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('filament-user-profile::messages.Two-Factor Authentication');
    }

    // This page is in a non-tenant panel, so isTenanted() is not needed

    public static function getSlug(?Panel $panel = null): string
    {
        return 'two-factor';
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
        return __('filament-user-profile::messages.Two-Factor Authentication');
    }

    public function getSubheading(): ?string
    {
        return __('filament-user-profile::messages.Add additional security to your account');
    }

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        // Check if database columns exist (fallback for direct URL access)
        if (!UserProfileHelper::hasTwoFactorColumns()) {
            Notification::make()
                ->danger()
                ->title(__('filament-user-profile::messages.Database Migration Required'))
                ->body(__('filament-user-profile::messages.The two-factor authentication columns are missing from the users table. Please run: php artisan vendor:publish --tag=filament-user-profile-migrations && php artisan migrate'))
                ->persistent()
                ->send();

            // Still allow page to load but it won't be functional
            $this->twoFactorEnabled = false;
            $this->requiresConfirmation = false;

            return;
        }

        $user = Auth::user();

        if (Fortify::confirmsTwoFactorAuthentication() && property_exists($user, 'two_factor_confirmed_at') && is_null($user->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($user);
        }

        $this->twoFactorEnabled = $this->checkTwoFactorEnabled($user);
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');

        $this->loadRecoveryCodes();
    }

    /**
     * Check if two-factor authentication is enabled for the user.
     */
    private function checkTwoFactorEnabled($user): bool
    {
        if (method_exists($user, 'hasEnabledTwoFactorAuthentication')) {
            try {
                return $user->hasEnabledTwoFactorAuthentication();
            } catch (\Exception) {
                // Method exists but might throw an error, fall through to fallback
            }
        }

        // Fallback: check if two_factor_secret exists and is not null
        if (property_exists($user, 'two_factor_secret')) {
            return !empty($user->two_factor_secret);
        }

        // If neither method nor property exists, 2FA is not enabled
        return false;
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        try {
            $enableTwoFactorAuthentication($user);

            if (!$this->requiresConfirmation) {
                $this->twoFactorEnabled = $this->checkTwoFactorEnabled($user);
            }

            $this->loadSetupData();

            $this->showModal = true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a missing column error
            if (str_contains($e->getMessage(), 'column') && str_contains($e->getMessage(), 'two_factor')) {
                Notification::make()
                    ->danger()
                    ->title(__('Database Migration Required'))
                    ->body(__('The two-factor authentication columns are missing from the users table. Please run: php artisan vendor:publish --tag=filament-user-profile-migrations && php artisan migrate'))
                    ->persistent()
                    ->send();
            } else {
                throw $e;
            }
        }
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = Auth::user();

        try {
            if (method_exists($user, 'twoFactorQrCodeSvg')) {
                $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            } else {
                throw new Exception('twoFactorQrCodeSvg method not found on user model');
            }

            if ($user->two_factor_secret) {
                $this->manualSetupKey = decrypt($user->two_factor_secret);
            } else {
                throw new Exception('Two factor secret not found');
            }
        } catch (Exception) {
            $this->addError('setupData', __('filament-user-profile::messages.Failed to fetch setup data.'));

            $this->qrCodeSvg = '';
            $this->manualSetupKey = '';

            Notification::make()
                ->danger()
                ->title(__('filament-user-profile::messages.Error'))
                ->body(__('filament-user-profile::messages.Failed to load two-factor authentication setup data. Please try again.'))
                ->send();
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        try {
            $confirmTwoFactorAuthentication(Auth::user(), $this->code);

            $this->closeModal();

            $this->twoFactorEnabled = true;
            $this->loadRecoveryCodes();

            Notification::make()
                ->success()
                ->title(__('filament-user-profile::messages.Two-Factor Authentication Enabled'))
                ->body(__('filament-user-profile::messages.Two-factor authentication has been successfully enabled for your account.'))
                ->send();
        } catch (Exception) {
            $this->addError('code', __('filament-user-profile::messages.Invalid verification code. Please try again.'));

            Notification::make()
                ->danger()
                ->title(__('filament-user-profile::messages.Verification Failed'))
                ->body(__('filament-user-profile::messages.The verification code you entered is invalid. Please try again.'))
                ->send();
        }
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->code = '';
        $this->showVerificationStep = false;

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->twoFactorEnabled = false;
        $this->recoveryCodes = [];
        $this->showRecoveryCodes = false;

        Notification::make()
            ->success()
            ->title(__('filament-user-profile::messages.Two-Factor Authentication Disabled'))
            ->body(__('filament-user-profile::messages.Two-factor authentication has been successfully disabled for your account.'))
            ->send();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->code = '';
        $this->showModal = false;
        $this->showVerificationStep = false;

        // Reset locked properties manually (can't use reset() on locked properties)
        $this->qrCodeSvg = '';
        $this->manualSetupKey = '';

        $this->resetErrorBag();

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = $this->checkTwoFactorEnabled(Auth::user());
            $this->loadRecoveryCodes();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('filament-user-profile::messages.Two-Factor Authentication Enabled'),
                'description' => __('filament-user-profile::messages.Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('filament-user-profile::messages.Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('filament-user-profile::messages.Verify Authentication Code'),
                'description' => __('filament-user-profile::messages.Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('filament-user-profile::messages.Continue'),
            ];
        }

        return [
            'title' => __('filament-user-profile::messages.Enable Two-Factor Authentication'),
            'description' => __('filament-user-profile::messages.To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('filament-user-profile::messages.Continue'),
        ];
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->loadRecoveryCodes();

        Notification::make()
            ->success()
            ->title(__('filament-user-profile::messages.Recovery Codes Regenerated'))
            ->body(__('filament-user-profile::messages.New recovery codes have been generated. Please save them in a secure location.'))
            ->send();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = Auth::user();

        if ($this->checkTwoFactorEnabled($user) && property_exists($user, 'two_factor_recovery_codes') && $user->two_factor_recovery_codes) {
            try {
                $decrypted = decrypt($user->two_factor_recovery_codes);
                $this->recoveryCodes = json_decode((string) $decrypted, true) ?: [];
            } catch (Exception) {
                $this->addError('recoveryCodes', __('filament-user-profile::messages.Failed to load recovery codes'));

                $this->recoveryCodes = [];
            }
        } else {
            $this->recoveryCodes = [];
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // No form components needed - using custom view
            ]);
    }

    public function submit(): void
    {
        // Not used - actions are handled via wire:click
    }

    // Navigation is enabled for the settings panel
}
