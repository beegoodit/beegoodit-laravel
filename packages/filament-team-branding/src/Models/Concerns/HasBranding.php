<?php

namespace BeeGoodIT\FilamentTeamBranding\Models\Concerns;

use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;

trait HasBranding
{
    use HasStoredFiles;

    /**
     * Get the public URL to the team's logo, or null if none.
     */
    public function getLogoUrl(): ?string
    {
        return $this->getFileUrl($this->logo ?? null);
    }

    /**
     * Get the logo URL for Filament (used in portal navbar).
     */
    public function getFilamentLogoUrl(): ?string
    {
        return $this->getLogoUrl();
    }

    /**
     * Scope a query to only include teams for a specific OAuth provider and tenant.
     */
    public function scopeWhereOAuthTenant($query, string $provider, string $tenantId)
    {
        return $query->where('oauth_provider', $provider)
            ->where('oauth_tenant_id', $tenantId);
    }
}

