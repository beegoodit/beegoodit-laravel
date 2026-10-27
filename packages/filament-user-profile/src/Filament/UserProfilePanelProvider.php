<?php

namespace BeegoodIT\FilamentUserProfile\Filament;

use BeegoodIT\FilamentOAuth\FilamentSocialitePluginHelper;
use BeegoodIT\FilamentUserProfile\Filament\Pages\Appearance;
use BeegoodIT\FilamentUserProfile\Filament\Pages\Dashboard;
use BeegoodIT\FilamentUserProfile\Filament\Pages\Notifications;
use BeegoodIT\FilamentUserProfile\Filament\Pages\Password;
use BeegoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeegoodIT\FilamentUserProfile\Filament\Pages\TwoFactor;
use BeegoodIT\FilamentUserProfile\UserProfileHelper;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Fortify\Features;

class UserProfilePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->id('me')
            ->path('me')
            ->login();

        if (config('filament-user-profile.registration', false)) {
            $panel->registration();
        }

        if (config('filament-user-profile.email_verification', false)) {
            $panel->emailVerification();
        }

        if (class_exists(FilamentSocialitePluginHelper::class)) {
            $providersList = [];
            $oauthProviders = config('filament-oauth.providers', []);

            if (data_get($oauthProviders, 'discord.enabled', false)) {
                $providersList[] = Provider::make('discord')
                    ->label('Discord')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text');
            }

            if (data_get($oauthProviders, 'microsoft.enabled', false)) {
                $providersList[] = Provider::make('microsoft')
                    ->label('Microsoft')
                    ->icon('heroicon-o-command-line');
            }

            if ($providersList !== []) {
                $panel->plugin(
                    FilamentSocialitePluginHelper::make()
                        ->providers($providersList)
                        ->registration(config('filament-user-profile.registration', false))
                );
            }
        }

        $panel
            // No tenant() - this panel has no tenancy
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Amber,
            ])
            ->brandName(fn () => __('filament-user-profile::messages.User Settings'))
            ->pages($this->getPages())
            ->navigationItems([
                NavigationItem::make()
                    ->label(fn () => __('filament-user-profile::messages.Back to Team Dashboard'))
                    ->icon('heroicon-o-arrow-left')
                    ->url(fn (): string => $this->getTenantPanelUrl())
                    ->sort(-1), // Show at the top
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                $this->getSetLocaleMiddleware(),
            ])
            ->authMiddleware(array_merge(
                [Authenticate::class],
                class_exists(\BeegoodIT\FilamentLegal\Http\Middleware\EnsureLegalAcceptance::class)
                ? [\BeegoodIT\FilamentLegal\Http\Middleware\EnsureLegalAcceptance::class]
                : []
            ))
            ->viteTheme((string) config(
                'filament-user-profile.tenant_panel_vite_theme',
                'resources/css/filament/portal/theme.css',
            ));

        $configure = config('filament-user-profile.configure_panel');

        if (is_string($configure) && class_exists($configure)) {
            $configure = app($configure);
        }

        if (is_callable($configure)) {
            $panel = $configure($panel) ?? $panel;
        }

        return $panel;
    }

    /**
     * URL to the configured tenant panel dashboard.
     * Handles both tenanted and non-tenanted panels.
     *
     * Since /me has no tenancy, resolve the Filament tenant from the authenticated user when needed.
     */
    protected function getTenantPanelUrl(): string
    {
        $panelId = (string) config('filament-user-profile.tenant_panel_id', 'portal');
        $panels = Filament::getPanels();

        if (! array_key_exists($panelId, $panels)) {
            return '/'.$panelId;
        }

        $tenantPanel = $panels[$panelId];

        if (! $tenantPanel->hasTenancy()) {
            return $tenantPanel->getUrl();
        }

        $user = Auth::user();

        if ($user) {
            $tenant = null;

            if (method_exists($user, 'currentTeam')) {
                $tenant = $user->currentTeam();
            } elseif (method_exists($user, 'teams')) {
                $tenant = $user->teams()->first();
            } elseif (method_exists($user, 'team')) {
                $tenant = $user->team;
            }

            if ($tenant) {
                return $tenantPanel->getUrl(tenant: $tenant);
            }
        }

        return $tenantPanel->getUrl();
    }

    /**
     * Get the list of pages to register.
     */
    protected function getPages(): array
    {
        $pages = [
            Dashboard::class,
            Profile::class,
            Password::class,
            Appearance::class,
        ];

        // Register Notifications page if push notification service is available
        if (class_exists(\BeegoodIT\LaravelPwa\Services\PushNotificationService::class)) {
            $pages[] = Notifications::class;
        }

        // Only register TwoFactor page if Fortify 2FA is enabled AND database columns exist
        if ($this->shouldRegisterTwoFactorPage()) {
            $pages[] = TwoFactor::class;
        }

        return $pages;
    }

    /**
     * Determine if the TwoFactor page should be registered.
     */
    protected function shouldRegisterTwoFactorPage(): bool
    {
        // Check if Fortify 2FA feature is enabled
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            return false;
        }

        // Check if required database columns exist
        return UserProfileHelper::hasTwoFactorColumns();
    }

    /**
     * Get the SetLocale middleware class.
     * Tries to use the app's SetLocale middleware first, falls back to the package's.
     */
    protected function getSetLocaleMiddleware(): string
    {
        // Prioritize package's robust SetLocale middleware
        if (class_exists(\BeegoodIT\FilamentI18n\Middleware\SetLocale::class)) {
            return \BeegoodIT\FilamentI18n\Middleware\SetLocale::class;
        }

        // Fall back to app's SetLocale middleware if it exists (legacy)
        if (class_exists(\App\Http\Middleware\SetLocale::class)) {
            return \App\Http\Middleware\SetLocale::class;
        }

        return \Illuminate\Routing\Middleware\SubstituteBindings::class;
    }
}
