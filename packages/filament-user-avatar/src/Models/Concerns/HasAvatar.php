<?php

namespace BeegoodIT\FilamentUserAvatar\Models\Concerns;

use BeegoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;

trait HasAvatar
{
    use HasStoredFiles;

    /**
     * Get the public URL to the user's avatar, or null if none.
     * Uses the HasStoredFiles trait's getFileUrl method.
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFileUrl($this->avatar ?? null);
    }

    /**
     * Get the avatar URL for Filament (used in portal navbar).
     * If no avatar exists, generates a fallback ui-avatars.com URL with hex color.
     * This ensures hex colors are used (not oklch) to prevent avatar URL issues.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        // If user has an avatar, return it
        $avatarUrl = $this->getAvatarUrl();
        if ($avatarUrl) {
            return $avatarUrl;
        }

        // Generate fallback ui-avatars.com URL with hex color
        // Use initials format (no spaces) - ui-avatars.com works better with concatenated initials
        $name = $this->initials();

        // Fallback if no initials (shouldn't happen, but be safe)
        if (empty($name)) {
            $name = 'U'; // Single letter fallback
        }

        // Get primary color as hex (ensures hex format, not oklch)
        $primaryColor = $this->getPrimaryColorForAvatar();

        // If no primary color, use default amber hex
        if (empty($primaryColor)) {
            $primaryColor = '#f59e0b';
        }

        // Ensure hex format (remove # for URL)
        $hexColor = ltrim((string) $primaryColor, '#');

        return 'https://ui-avatars.com/api/?name='.urlencode((string) $name)
            .'&color=FFFFFF&background='.$hexColor;
    }

    /**
     * Get the primary color for avatar URLs.
     * Tries to get color from tenant (if filament-tenancy is used), then falls back to panel default.
     * Always returns hex format (never oklch) to prevent avatar URL issues.
     *
     * @return string|null Hex color string (e.g., '#f59e0b') or null
     */
    protected function getPrimaryColorForAvatar(): ?string
    {
        // Try to get color from tenant (if filament-tenancy is used)
        try {
            $tenant = filament()->getTenant();
            if ($tenant) {
                // Try to use the accessor first (if HasBranding trait is used)
                // The accessor will convert oklch to hex automatically
                if (method_exists($tenant, 'getPrimaryColorAttribute') ||
                    in_array(\BeegoodIT\FilamentTenancy\Models\Concerns\HasBranding::class, class_uses_recursive($tenant))) {
                    $color = $tenant->primary_color;
                    if (! empty($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $color)) {
                        return $color;
                    }
                }

                // Fallback: check raw attribute and convert if needed
                if (isset($tenant->attributes['primary_color'])) {
                    $rawColor = $tenant->attributes['primary_color'];
                    if (! empty($rawColor)) {
                        // If already hex, return it
                        if (preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $rawColor)) {
                            return $rawColor;
                        }
                        // If oklch, we can't easily convert here without the HasBranding method
                        // So return null to use default
                    }
                }
            }
        } catch (\Exception) {
            // If filament() is not available or tenant doesn't exist, continue to fallback
        }

        return null; // Will use default amber
    }

    /**
     * Get the user's initials for fallback display.
     */
    public function initials(): string
    {
        return \Illuminate\Support\Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
            ->implode('');
    }
}
