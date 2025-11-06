<?php

namespace BeeGoodIT\FilamentTeamBranding\Models\Concerns;

use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;
use Filament\Support\Colors\Color;

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
     * Get the avatar URL for Filament tenant menu.
     * Overrides Filament's default to ensure hex colors are used (not oklch).
     *
     * @return string Avatar URL with hex background color
     */
    public function getFilamentAvatarUrl(): ?string
    {
        // Use logo if available
        $logoUrl = $this->getLogoUrl();
        if ($logoUrl) {
            return $logoUrl;
        }

        // Generate ui-avatars.com URL with hex color
        $name = str($this->name ?? '')
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        // Get primary color as hex (accessor ensures hex format)
        $primaryColor = $this->primary_color;
        
        // If no primary color, use default amber hex
        if (empty($primaryColor)) {
            $primaryColor = '#f59e0b';
        }

        // Ensure hex format (remove # for URL)
        $hexColor = ltrim($primaryColor, '#');

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) 
            . '&color=FFFFFF&background=' . $hexColor;
    }

    /**
     * Get the primary color as a hex value.
     * Ensures oklch colors are converted to hex for avatar URLs and other uses.
     * Always returns hex (never oklch) to prevent avatar URL issues.
     *
     * @param mixed $value The raw value from the database
     * @return string|null Hex color string (e.g., '#f59e0b') or null
     */
    public function getPrimaryColorAttribute($value)
    {
        // Get the raw value from attributes (bypassing accessor to avoid recursion)
        $rawValue = $this->attributes['primary_color'] ?? $value;

        // If null or empty, return null (Filament will use panel default)
        if (empty($rawValue)) {
            return null;
        }

        $rawValue = (string) $rawValue;

        // If already hex, return as-is
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $rawValue)) {
            return $rawValue;
        }

        // If oklch format, convert to hex
        if (str_starts_with($rawValue, 'oklch')) {
            // Extract oklch values: oklch(L C H) or oklch(L C H / alpha)
            if (preg_match('/oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)/', $rawValue, $matches)) {
                $l = (float) $matches[1];
                $c = (float) $matches[2];
                $h = (float) $matches[3];

                // Convert oklch to rgb, then to hex
                try {
                    $rgb = $this->oklchToRgb($l, $c, $h);
                    return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                } catch (\Exception $e) {
                    // If conversion fails, return null
                    return null;
                }
            }
        }

        // If we can't parse it, return null
        return null;
    }

    /**
     * Set the primary color, ensuring it's stored as hex.
     *
     * @param mixed $value
     */
    public function setPrimaryColorAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['primary_color'] = null;
            return;
        }

        $value = (string) $value;

        // If already hex, store as-is
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            $this->attributes['primary_color'] = $value;
            return;
        }

        // If oklch, convert to hex before storing
        if (str_starts_with($value, 'oklch')) {
            if (preg_match('/oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)/', $value, $matches)) {
                try {
                    $rgb = $this->oklchToRgb((float) $matches[1], (float) $matches[2], (float) $matches[3]);
                    $hex = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                    $this->attributes['primary_color'] = $hex;
                    return;
                } catch (\Exception $e) {
                    // If conversion fails, store as null
                    $this->attributes['primary_color'] = null;
                    return;
                }
            }
        }

        // If we can't parse it, store as null
        $this->attributes['primary_color'] = null;
    }

    /**
     * Convert oklch to RGB (simplified conversion).
     * For production, consider using a proper color conversion library.
     *
     * @param float $l Lightness (0-1)
     * @param float $c Chroma
     * @param float $h Hue (0-360)
     * @return array [r, g, b] values (0-255)
     */
    protected function oklchToRgb(float $l, float $c, float $h): array
    {
        // Convert oklch to lab first
        $a = $c * cos(deg2rad($h));
        $b = $c * sin(deg2rad($h));

        // Convert lab to rgb (simplified, using D65 white point)
        // This is a basic approximation - for accurate conversion, use a library
        $y = ($l + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        $x = 0.95047 * (($x > 0.206897) ? pow($x, 3) : ($x - 16/116) / 7.787);
        $y = 1.00000 * (($y > 0.206897) ? pow($y, 3) : ($y - 16/116) / 7.787);
        $z = 1.08883 * (($z > 0.206897) ? pow($z, 3) : ($z - 16/116) / 7.787);

        $r = $x * 3.2406 + $y * -1.5372 + $z * -0.4986;
        $g = $x * -0.9689 + $y * 1.8758 + $z * 0.0415;
        $b = $x * 0.0557 + $y * -0.2040 + $z * 1.0570;

        $r = max(0, min(255, round($r * 255)));
        $g = max(0, min(255, round($g * 255)));
        $b = max(0, min(255, round($b * 255)));

        return [$r, $g, $b];
    }

    /**
     * Get the primary color as hex for avatar URLs.
     * This ensures avatar URLs always use hex format (not oklch).
     *
     * @return string|null Hex color string (e.g., '#f59e0b') or null
     */
    public function getPrimaryColorForAvatar(): ?string
    {
        // Use the accessor which ensures hex format
        return $this->primary_color;
    }
}

