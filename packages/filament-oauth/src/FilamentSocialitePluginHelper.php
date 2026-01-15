<?php

namespace BeeGoodIT\FilamentOAuth;

use BeeGoodIT\FilamentOAuth\Models\SocialiteUser;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;

class FilamentSocialitePluginHelper
{
    /**
     * Create a configured FilamentSocialitePlugin with automatic email verification.
     */
    public static function make(): FilamentSocialitePlugin
    {
        $userModel = config('auth.providers.users.model');

        return FilamentSocialitePlugin::make()
            ->socialiteUserModelClass(SocialiteUser::class)
            ->createUserUsing(function (string $provider, $oauthUser, $plugin) use ($userModel) {
                // Create user with email verified (OAuth providers verify emails)
                $user = $userModel::create([
                    'name' => $oauthUser->getName(),
                    'email' => $oauthUser->getEmail(),
                    'email_verified_at' => now(),
                ]);

                // Sync avatar if enabled
                if (config('filament-oauth.sync_avatars', false)) {
                    app(\BeeGoodIT\FilamentOAuth\Services\AvatarService::class)->syncAvatar($user, $oauthUser);
                }

                // Assign team DURING user creation (inside the transaction) if enabled for this provider
                $providerConfig = config("filament-oauth.providers.{$provider}");
                $shouldAssignTeam = $providerConfig['team_assignment'] ?? false;

                if ($shouldAssignTeam && config('filament-oauth.auto_assign_teams', true)) {
                    $tenantId = null;
                    if ($provider === 'microsoft') {
                        $tenantId = self::extractMicrosoftTenantId($oauthUser);
                    }
                    // Add other providers here if they support tenant-based assignment
                    
                    $accessToken = $oauthUser->token ?? null;

                    if ($tenantId) {
                        $teamAssignmentService = app(\BeeGoodIT\FilamentOAuth\Services\TeamAssignmentService::class);
                        $teamAssignmentService->assignUserToTeam($user, $provider, $tenantId, $accessToken);

                        // Critical: Ensure teams relationship is loaded on the user instance
                        // before returning it, so it's available when Filament checks for tenants
                        $user->load('teams');
                    }
                }

                return $user;
            })
            ->redirectAfterLoginUsing(function (string $provider, $socialiteUser, $plugin) {
                // Teams are now assigned during user creation, but we need to ensure
                // the user instance has teams loaded when checking for tenants
                $user = $socialiteUser->getUser();

                // Ensure teams relationship is loaded (it might not be if getUser() returned a fresh instance)
                if (!$user->relationLoaded('teams')) {
                    $user->load('teams');
                }

                // Now use the default Filament redirect logic
                $panel = $plugin->getPanel();
                if ($panel->hasTenancy()) {
                    $tenant = \Filament\Facades\Filament::getUserDefaultTenant($user);

                    // Redirect to 2FA challenge if enabled
                    if ($user->two_factor_secret && !session()->get('auth.two_factor_confirmed_at')) {
                        return redirect()->route('filament.user-profile.pages.two-factor-challenge');
                    }

                    if (is_null($tenant) && $tenantRegistrationUrl = $panel->getTenantRegistrationUrl()) {
                        return redirect()->intended($tenantRegistrationUrl);
                    }

                    return redirect()->intended(
                        $panel->getUrl($tenant)
                    );
                }

                return redirect()->intended($panel->getUrl());
            });
    }

    /**
     * Extract tenant ID from Microsoft OAuth user data.
     */
    protected static function extractMicrosoftTenantId($oauthUser): ?string
    {
        // Try to get from user data
        $tenantId = $oauthUser->user['tid'] ?? null;

        // If not in user data, try access token response
        if (!$tenantId && isset($oauthUser->accessTokenResponseBody)) {
            $tokenData = $oauthUser->accessTokenResponseBody;
            $tenantId = $tokenData['tenant_id'] ?? $tokenData['tid'] ?? null;
        }

        // If still no tenant ID, try to decode the JWT token
        if (!$tenantId && isset($oauthUser->token)) {
            try {
                $tokenParts = explode('.', $oauthUser->token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    $tenantId = $payload['tid'] ?? $payload['tenant_id'] ?? null;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to decode JWT token for tenant ID extraction', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $tenantId;
    }
}
