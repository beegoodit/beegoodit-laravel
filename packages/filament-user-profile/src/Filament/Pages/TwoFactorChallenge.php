<?php

namespace BeegoodIT\FilamentUserProfile\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TwoFactorChallenge extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $code = null;
    public ?string $recovery_code = null;
    public bool $usingRecoveryCode = false;

    protected static string $view = 'filament-user-profile::pages.two-factor-challenge';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        // If user doesn't have 2FA enabled, redirect them home
        if (!Auth::user()?->hasEnabledTwoFactorAuthentication()) {
            redirect()->intended(config('fortify.home'));
        }

        // If user is already "confirmed" in this session, redirect home
        if (session()->get('auth.two_factor_confirmed_at')) {
            redirect()->intended(config('fortify.home'));
        }
    }

    public function toggleRecoveryCode(): void
    {
        $this->usingRecoveryCode = !$this->usingRecoveryCode;
        $this->code = null;
        $this->recovery_code = null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make($this->usingRecoveryCode ? 'recovery_code' : 'code')
                    ->label($this->usingRecoveryCode ? __('filament-user-profile::messages.Recovery Code') : __('filament-user-profile::messages.Authentication Code'))
                    ->placeholder($this->usingRecoveryCode ? 'abcdef-12345' : '000000')
                    ->required()
                    ->autofocus(),
            ]);
    }

    public function confirm(): void
    {
        $user = Auth::user();
        $data = $this->form->getState();

        $code = $data['code'] ?? null;
        $recovery_code = $data['recovery_code'] ?? null;

        if ($recovery_code) {
            if ($user->replaceRecoveryCode($recovery_code)) {
                $this->finishConfirmation();
                return;
            }
        } elseif ($code) {
            if ($user->confirmTwoFactorAuthentication($code)) {
                $this->finishConfirmation();
                return;
            }
        }

        Notification::make()
            ->danger()
            ->title(__('filament-user-profile::messages.Invalid Code'))
            ->body(__('filament-user-profile::messages.The provided two-factor authentication code was invalid.'))
            ->send();
    }

    protected function finishConfirmation(): void
    {
        session()->put('auth.two_factor_confirmed_at', now()->timestamp);

        Notification::make()
            ->success()
            ->title(__('filament-user-profile::messages.Authentication Success'))
            ->send();

        redirect()->intended(config('fortify.home'));
    }
}
