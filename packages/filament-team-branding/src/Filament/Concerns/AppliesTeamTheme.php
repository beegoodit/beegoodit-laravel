<?php

namespace BeeGoodIT\FilamentTeamBranding\Filament\Concerns;

use BeeGoodIT\FilamentTeamBranding\Filament\ThemeRenderer;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

trait AppliesTeamTheme
{
    /**
     * Apply dynamic team theming to the panel via CSS variables.
     * 
     * This method adds a render hook that injects CSS variables for tenant colors,
     * allowing dynamic theming while keeping the panel's default color static
     * (which prevents issues with avatar URLs and other Filament components).
     *
     * @param Panel $panel The Filament panel instance
     * @param string $defaultPrimaryColor Hex color string for default primary color (e.g., '#00ffff')
     * @param string|null $defaultSecondaryColor Optional hex color string for default secondary color
     * @return Panel The panel instance with theming applied
     */
    public function applyTeamTheme(Panel $panel, string $defaultPrimaryColor, ?string $defaultSecondaryColor = null): Panel
    {
        return $panel->renderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(
                ThemeRenderer::getTemplate($defaultPrimaryColor, $defaultSecondaryColor)
            )
        );
    }
}

