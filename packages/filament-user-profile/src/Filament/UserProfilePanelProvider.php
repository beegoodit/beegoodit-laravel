<?php

namespace BeeGoodIT\FilamentUserProfile\Filament;

use BeeGoodIT\FilamentUserProfile\Filament\Pages\Appearance;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Password;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\TwoFactor;
use BeeGoodIT\FilamentUserProfile\UserProfileHelper;
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
        return $panel
            ->id('user-profile')
            ->path('settings')
            ->login(false) // No login - users access from main panel
            // No tenant() - this panel has no tenancy
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Amber,
            ])
            ->brandName('User Settings')
            ->pages($this->getPages())
            ->navigationItems([
                NavigationItem::make()
                    ->label(__('Back to Portal'))
                    ->icon('heroicon-o-arrow-left')
                    ->url(fn () => $this->getPortalUrl())
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/portal/theme.css');
    }

    /**
     * Get the URL to the portal panel dashboard.
     * Handles both tenant and non-tenant portals.
     *
     * Since the settings panel doesn't have tenancy, we need to get the tenant
     * from the authenticated user's teams.
     */
    protected function getPortalUrl(): string
    {
        $portalPanel = Filament::getPanel('portal');

        if (! $portalPanel) {
            // Fallback if portal panel doesn't exist
            return '/portal';
        }

        // If portal doesn't have tenancy, just return the base URL
        if (! $portalPanel->hasTenancy()) {
            return $portalPanel->getUrl();
        }

        // Try to get tenant from authenticated user's teams
        $user = Auth::user();

        if ($user) {
            // Try to get the user's first team (or default team if method exists)
            $team = null;

            if (method_exists($user, 'currentTeam')) {
                $team = $user->currentTeam();
            } elseif (method_exists($user, 'teams')) {
                $team = $user->teams()->first();
            } elseif (method_exists($user, 'team')) {
                $team = $user->team;
            }

            if ($team) {
                return $portalPanel->getUrl(tenant: $team);
            }
        }

        // Fallback: return base portal URL (will redirect to team selection if needed)
        return $portalPanel->getUrl();
    }

    /**
     * Get the list of pages to register.
     */
    protected function getPages(): array
    {
        $pages = [
            Profile::class,
            Password::class,
            Appearance::class,
        ];

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
}
