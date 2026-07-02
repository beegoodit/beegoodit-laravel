<?php

namespace BeegoodIT\LaravelPwa\Tests\Feature;

use BeegoodIT\LaravelPwa\Support\PwaThemeTokens;
use BeegoodIT\LaravelPwa\Tests\TestCase;

class PwaNavAssetsTest extends TestCase
{
    public function test_pwa_nav_stylesheet_is_served_from_package(): void
    {
        $response = $this->get('/vendor/laravel-pwa/pwa-nav.css');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/css; charset=UTF-8');
        $response->assertSee('.pwa-nav__bar', false);
        $response->assertSee('--pwa-surface', false);
        $response->assertSee('[x-cloak]', false);
        $response->assertDontSee('prefers-color-scheme', false);
        $response->assertDontSee(":root {\n    --pwa-surface:", false);
    }

    public function test_pwa_styles_renders_stylesheet_before_config_theme_tokens(): void
    {
        config()->set('pwa.navigation.theme_tokens', [
            'light' => ['text-active' => '#f59e0b'],
            'dark' => [],
        ]);

        $html = view('laravel-pwa::partials.pwa-styles')->render();

        $navCssPos = strpos($html, 'pwa-nav.css?v=');
        $themeTokensPos = strpos($html, '--pwa-text-active: #f59e0b;');

        $this->assertNotFalse($navCssPos);
        $this->assertNotFalse($themeTokensPos);
        $this->assertLessThan($themeTokensPos, $navCssPos);
    }

    public function test_pwa_styles_directive_renders_stylesheet_links_with_cache_busting(): void
    {
        $html = view('laravel-pwa::partials.pwa-styles')->render();

        $this->assertStringContainsString('pwa-nav.css?v=', $html);
        $this->assertStringContainsString('push-prompt.css?v=', $html);
    }

    public function test_pwa_theme_tokens_render_from_config(): void
    {
        config()->set('pwa.navigation.theme_tokens', [
            'light' => ['text-active' => '#f59e0b'],
            'dark' => ['text-active' => '#fbbf24'],
        ]);

        $html = view('laravel-pwa::partials.pwa-theme-tokens')->render();

        $this->assertStringContainsString(':root {', $html);
        $this->assertStringContainsString('--pwa-text-active: #f59e0b;', $html);
        $this->assertStringContainsString('.dark {', $html);
        $this->assertStringContainsString('--pwa-text-active: #fbbf24;', $html);
    }

    public function test_pwa_theme_tokens_render_nothing_when_config_empty(): void
    {
        config()->set('pwa.navigation.theme_tokens', [
            'light' => [],
            'dark' => [],
        ]);

        $html = view('laravel-pwa::partials.pwa-theme-tokens')->render();

        $this->assertSame('', trim($html));
    }

    public function test_pwa_nav_component_renders_semantic_markup(): void
    {
        $html = view('laravel-pwa::components.pwa-nav', [
            'items' => [
                [
                    'label' => 'Home',
                    'icon' => 'heroicon-o-home',
                    'url' => '/',
                    'active' => true,
                ],
            ],
            'menuTitle' => 'Menu',
        ])->render();

        $this->assertStringContainsString('class="pwa-nav pwa-nav--with-padding"', $html);
        $this->assertStringContainsString('style="--pwa-nav-padding-bottom: 4rem"', $html);
        $this->assertStringContainsString('id="pwa-navigation-bar"', $html);
        $this->assertStringContainsString('pwa-nav__bar', $html);
        $this->assertStringContainsString('pwa-nav__sheet', $html);
        $this->assertStringContainsString('x-cloak', $html);
    }

    public function test_theme_token_helper_normalizes_variable_names(): void
    {
        $this->assertSame('--pwa-text-active', PwaThemeTokens::cssVariableName('text-active'));
        $this->assertSame('--pwa-text-active', PwaThemeTokens::cssVariableName('pwa-text-active'));
        $this->assertSame('--pwa-sheet-border', PwaThemeTokens::cssVariableName('sheet_border'));
    }
}
