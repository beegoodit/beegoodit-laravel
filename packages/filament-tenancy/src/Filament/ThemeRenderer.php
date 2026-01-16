<?php

namespace BeeGoodIT\FilamentTenancy\Filament;

use Filament\Support\Colors\Color;

class ThemeRenderer
{
    /**
     * Generate the Blade template string for dynamic team theming via CSS variables.
     *
     * @param  string  $defaultPrimaryColor  Hex color string (e.g., '#00ffff')
     * @param  string|null  $defaultSecondaryColor  Optional hex color string
     * @return string Blade template string
     */
    public static function renderHook(string $defaultPrimaryColor, ?string $defaultSecondaryColor = null): string
    {
        return <<<'BLADE'
@php
    try {
        $tenant = filament()->getTenant();
        $primaryColor = $tenant?->primary_color;
        $secondaryColor = $tenant?->secondary_color;
        
        $cssVars = [];
        
        // Always set primary color (tenant or default)
        if ($primaryColor) {
            $primaryPalette = \Filament\Support\Colors\Color::hex($primaryColor);
        } else {
            $primaryPalette = \Filament\Support\Colors\Color::hex('{{ $defaultPrimaryColor }}');
        }
        foreach ($primaryPalette as $shade => $oklch) {
            $cssVars["--primary-{$shade}"] = $oklch;
        }
        
        // Set secondary color if exists
        if ($secondaryColor) {
            $secondaryPalette = \Filament\Support\Colors\Color::hex($secondaryColor);
            foreach ($secondaryPalette as $shade => $oklch) {
                $cssVars["--secondary-{$shade}"] = $oklch;
            }
        } else if ({{ $defaultSecondaryColor ? "'{$defaultSecondaryColor}'" : 'null' }}) {
            // Use default secondary color if provided
            $secondaryPalette = \Filament\Support\Colors\Color::hex('{{ $defaultSecondaryColor ?? '' }}');
            foreach ($secondaryPalette as $shade => $oklch) {
                $cssVars["--secondary-{$shade}"] = $oklch;
            }
        }
    } catch (\Throwable $e) {
        // Fallback to default primary color
        $cssVars = [];
        $primaryPalette = \Filament\Support\Colors\Color::hex('{{ $defaultPrimaryColor }}');
        foreach ($primaryPalette as $shade => $oklch) {
            $cssVars["--primary-{$shade}"] = $oklch;
        }
    }
@endphp
<style>
    :root {
        @foreach($cssVars as $var => $value)
        {{ $var }}: {{ $value }};
        @endforeach
    }
</style>
BLADE;
    }

    /**
     * Generate the Blade template string with proper variable substitution.
     * This method handles the Blade compilation correctly.
     *
     * @param  string  $defaultPrimaryColor  Hex color string (e.g., '#00ffff')
     * @param  string|null  $defaultSecondaryColor  Optional hex color string
     * @return string Blade template string ready for rendering
     */
    public static function getTemplate(string $defaultPrimaryColor, ?string $defaultSecondaryColor = null): string
    {
        // Escape the color strings for use in PHP strings
        $primaryColorEscaped = addslashes($defaultPrimaryColor);

        // Build secondary color section conditionally
        $secondarySection = '';
        if ($defaultSecondaryColor) {
            $secondaryColorEscaped = addslashes($defaultSecondaryColor);
            $secondarySection = <<<BLADE
        } else {
            // Use default secondary color if provided
            \$secondaryPalette = \\Filament\\Support\\Colors\\Color::hex('{$secondaryColorEscaped}');
            foreach (\$secondaryPalette as \$shade => \$oklch) {
                \$cssVars["--secondary-{\$shade}"] = \$oklch;
            }
        }
BLADE;
        } else {
            $secondarySection = "\n        }";
        }

        $avatarFixScript = self::getAvatarUrlFixScript();

        return <<<BLADE
@php
    try {
        \$tenant = filament()->getTenant();
        \$primaryColor = \$tenant?->primary_color;
        \$secondaryColor = \$tenant?->secondary_color;
        
        \$cssVars = [];
        
        // Always set primary color (tenant or default)
        if (\$primaryColor) {
            \$primaryPalette = \\Filament\\Support\\Colors\\Color::hex(\$primaryColor);
        } else {
            \$primaryPalette = \\Filament\\Support\\Colors\\Color::hex('{$primaryColorEscaped}');
        }
        foreach (\$primaryPalette as \$shade => \$oklch) {
            \$cssVars["--primary-{\$shade}"] = \$oklch;
        }
        
        // Set secondary color if exists
        if (\$secondaryColor) {
            \$secondaryPalette = \\Filament\\Support\\Colors\\Color::hex(\$secondaryColor);
            foreach (\$secondaryPalette as \$shade => \$oklch) {
                \$cssVars["--secondary-{\$shade}"] = \$oklch;
            }
{$secondarySection}
    } catch (\\Throwable \$e) {
        // Fallback to default primary color
        \$cssVars = [];
        \$primaryPalette = \\Filament\\Support\\Colors\\Color::hex('{$primaryColorEscaped}');
        foreach (\$primaryPalette as \$shade => \$oklch) {
            \$cssVars["--primary-{\$shade}"] = \$oklch;
        }
    }
@endphp
<style>
    :root {
        @foreach(\$cssVars as \$var => \$value)
        {{\$var}}: {{\$value}};
        @endforeach
    }
</style>
{$avatarFixScript}
BLADE;
    }

    /**
     * Get JavaScript to convert oklch colors to hex in avatar URLs.
     * This fixes avatar URLs that use oklch format (which ui-avatars.com doesn't support).
     *
     * @return string JavaScript code
     */
    public static function getAvatarUrlFixScript(): string
    {
        return <<<'JS'
<script>
(function() {
    // Function to convert oklch to hex
    function oklchToHex(oklch) {
        // Parse oklch(L C H) format
        const match = oklch.match(/oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)/);
        if (!match) return null;
        
        const l = parseFloat(match[1]);
        const c = parseFloat(match[2]);
        const h = parseFloat(match[3]);
        
        // Convert oklch to rgb (simplified conversion)
        const a = c * Math.cos(h * Math.PI / 180);
        const b = c * Math.sin(h * Math.PI / 180);
        
        // Convert oklch to rgb (approximate)
        let y = (l + 16) / 116;
        let x = a / 500 + y;
        let z = y - b / 200;
        
        x = 0.95047 * ((x > 0.206897) ? Math.pow(x, 3) : (x - 16/116) / 7.787);
        y = 1.00000 * ((y > 0.206897) ? Math.pow(y, 3) : (y - 16/116) / 7.787);
        z = 1.08883 * ((z > 0.206897) ? Math.pow(z, 3) : (z - 16/116) / 7.787);
        
        let r = x * 3.2406 + y * -1.5372 + z * -0.4986;
        let g = x * -0.9689 + y * 1.8758 + z * 0.0415;
        let bl = x * 0.0557 + y * -0.2040 + z * 1.0570;
        
        r = Math.max(0, Math.min(255, Math.round(r * 255)));
        g = Math.max(0, Math.min(255, Math.round(g * 255)));
        bl = Math.max(0, Math.min(255, Math.round(bl * 255)));
        
        return '#' + [r, g, bl].map(x => {
            const hex = x.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
    }
    
    // Fix avatar URLs on page load
    function fixAvatarUrls() {
        // Fix all ui-avatars.com URLs (both tenant and user avatars)
        document.querySelectorAll('img[src*="ui-avatars.com"]').forEach(img => {
            const src = img.src;
            if (src.includes('background=oklch')) {
                const match = src.match(/background=([^&]+)/);
                if (match) {
                    const oklch = decodeURIComponent(match[1]);
                    const hex = oklchToHex(oklch);
                    if (hex) {
                        img.src = src.replace(/background=[^&]+/, 'background=' + hex.replace('#', ''));
                    }
                }
            }
        });
    }
    
    // Run on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixAvatarUrls);
    } else {
        fixAvatarUrls();
    }
    
    // Also watch for dynamically added avatars
    const observer = new MutationObserver(fixAvatarUrls);
    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>
JS;
    }
}
