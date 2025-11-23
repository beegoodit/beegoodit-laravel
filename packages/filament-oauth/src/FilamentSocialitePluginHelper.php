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
                return $userModel::create([
                    'name' => $oauthUser->getName(),
                    'email' => $oauthUser->getEmail(),
                    'email_verified_at' => now(),
                ]);
            });
    }
}
