<?php

namespace BeeGoodIT\FilamentOAuth\Models\Concerns;

use BeeGoodIT\FilamentOAuth\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasOAuth
{
    /**
     * Get the OAuth accounts for the user.
     */
    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(OAuthAccount::class);
    }

    /**
     * Get the OAuth account for a specific provider.
     */
    public function getOAuthAccount(string $provider): ?OAuthAccount
    {
        return $this->oauthAccounts()->whereProvider($provider)->first();
    }

    /**
     * Check if the user has an OAuth account for a specific provider.
     */
    public function hasOAuthProvider(string $provider): bool
    {
        return $this->oauthAccounts()->whereProvider($provider)->exists();
    }

    /**
     * Check if the user is OAuth-only (has no password).
     */
    public function isOAuthOnly(): bool
    {
        return is_null($this->password);
    }
}
