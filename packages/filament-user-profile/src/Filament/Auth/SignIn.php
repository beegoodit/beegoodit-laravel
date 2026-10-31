<?php

namespace BeegoodIT\FilamentUserProfile\Filament\Auth;

use BeegoodIT\FilamentUserProfile\Auth\EmailSignInCode;
use BeegoodIT\FilamentUserProfile\Enums\SignInMethod;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignIn extends Login
{
    public string $method = 'password';

    public bool $codeSent = false;

    public function mount(): void
    {
        parent::mount();

        $this->method = SignInMethod::primary()->value;
    }

    public function authenticate(): ?LoginResponse
    {
        if ($this->showingPassword()) {
            if (! SignInMethod::enabled(SignInMethod::Password)) {
                $this->method = SignInMethod::EmailCode->value;

                return null;
            }

            return parent::authenticate();
        }

        if (! SignInMethod::enabled(SignInMethod::EmailCode)) {
            $this->method = SignInMethod::Password->value;

            return null;
        }

        if (! $this->codeSent) {
            $this->sendCode();

            return null;
        }

        return $this->verifyCode();
    }

    public function resend(): void
    {
        $this->sendCode();
    }

    public function showPassword(): void
    {
        if (! SignInMethod::enabled(SignInMethod::Password)) {
            return;
        }

        $this->method = SignInMethod::Password->value;
        $this->codeSent = false;
    }

    public function showCode(): void
    {
        if (! SignInMethod::enabled(SignInMethod::EmailCode)) {
            return;
        }

        $this->method = SignInMethod::EmailCode->value;
        $this->codeSent = false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent()
                    ->readOnly(fn (): bool => $this->codeSent && ! $this->showingPassword()),
                TextInput::make('code')
                    ->label(__('filament-user-profile::auth.email_code.code_heading'))
                    ->visible(fn (): bool => $this->codeSent && ! $this->showingPassword())
                    ->required(fn (): bool => $this->codeSent && ! $this->showingPassword())
                    ->rules(['digits:6'])
                    ->autocomplete('one-time-code')
                    ->extraInputAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*', 'maxlength' => '6'])
                    ->autofocus(fn (): bool => $this->codeSent),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent()
                    ->visible(fn (): bool => $this->showingPassword())
                    ->dehydrated(fn (): bool => $this->showingPassword()),
            ]);
    }

    public function getHeading(): string|Htmlable|null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getHeading();
        }

        if ($this->showingPassword()) {
            return __('filament-user-profile::auth.email_code.password_heading');
        }

        if ($this->codeSent) {
            return __('filament-user-profile::auth.email_code.code_heading');
        }

        return __('filament-user-profile::auth.email_code.heading');
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getSubheading();
        }

        if ($this->showingPassword()) {
            if (! filament()->hasRegistration()) {
                return null;
            }

            return new HtmlString(__('filament-panels::auth/pages/login.actions.register.before').' '.$this->registerAction->toHtml());
        }

        if ($this->codeSent) {
            return __('filament-user-profile::auth.email_code.code_subheading');
        }

        if (! filament()->hasRegistration()) {
            return __('filament-user-profile::auth.email_code.subheading_sign_in');
        }

        return new HtmlString(
            e(__('filament-user-profile::auth.email_code.subheading_register')).' '.$this->registerAction->toHtml()
        );
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->hint(filament()->hasPasswordReset()
                ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()"> {{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }}</x-filament::link>'))
                : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->visible(fn (): bool => $this->showingPassword())
            ->required(fn (): bool => $this->showingPassword())
            ->dehydrated(fn (): bool => $this->showingPassword());
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
            Action::make('resendCode')
                ->link()
                ->label(__('filament-user-profile::auth.email_code.resend'))
                ->visible(fn (): bool => $this->codeSent && ! $this->showingPassword())
                ->action(fn () => $this->resend()),
            Action::make('changeEmail')
                ->link()
                ->label(__('filament-user-profile::auth.email_code.change_email'))
                ->visible(fn (): bool => $this->codeSent && ! $this->showingPassword())
                ->action(function (): void {
                    $this->codeSent = false;
                }),
            Action::make('showPassword')
                ->link()
                ->label(__('filament-user-profile::auth.email_code.use_password'))
                ->visible(fn (): bool => ! $this->showingPassword() && SignInMethod::enabled(SignInMethod::Password))
                ->action(fn () => $this->showPassword()),
            Action::make('showCode')
                ->link()
                ->label(__('filament-user-profile::auth.email_code.use_code'))
                ->visible(fn (): bool => $this->showingPassword() && SignInMethod::enabled(SignInMethod::EmailCode))
                ->action(fn () => $this->showCode()),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(function (): string {
                if ($this->showingPassword()) {
                    return __('filament-panels::auth/pages/login.form.actions.authenticate.label');
                }

                if ($this->codeSent) {
                    return __('filament-user-profile::auth.email_code.sign_in');
                }

                return __('filament-user-profile::auth.email_code.send');
            })
            ->submit('authenticate');
    }

    protected function sendCode(): void
    {
        app(EmailSignInCode::class)->send($this->validatedEmail());

        $this->codeSent = true;
        $this->method = SignInMethod::EmailCode->value;

        Notification::make()
            ->title(__('filament-user-profile::auth.email_code.sent_title'))
            ->body(__('filament-user-profile::auth.email_code.sent_body'))
            ->success()
            ->send();
    }

    protected function verifyCode(): LoginResponse
    {
        $email = $this->validatedEmail();
        $code = (string) ($this->data['code'] ?? '');

        $validator = validator(
            ['code' => $code],
            ['code' => ['required', 'digits:6']],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'data.code' => $validator->errors()->get('code'),
            ]);
        }

        $user = app(EmailSignInCode::class)->consume($email, $code);

        if (! $user instanceof Authenticatable) {
            throw ValidationException::withMessages([
                'data.code' => __('filament-user-profile::auth.email_code.invalid'),
            ]);
        }

        if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
            ]);
        }

        Filament::auth()->login($user);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function validatedEmail(): string
    {
        $email = $this->data['email'] ?? null;

        $validator = validator(
            ['email' => $email],
            ['email' => ['required', 'string', 'email', 'max:255']],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'data.email' => $validator->errors()->get('email'),
            ]);
        }

        return Str::lower(trim((string) $email));
    }

    protected function showingPassword(): bool
    {
        return $this->method === SignInMethod::Password->value;
    }
}
