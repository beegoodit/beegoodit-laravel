<?php

namespace BeeGoodIT\FilamentI18n\View\Components;

use Illuminate\View\Component;

class LanguageSwitcher extends Component
{
    public array $locales;
    public string $currentLocale;
    public ?string $routeBase;

    public function __construct(?string $routeBase = null)
    {
        $this->locales = config('filament-i18n.available_locales', ['en']);
        $this->currentLocale = app()->getLocale();
        $this->routeBase = $routeBase ?? $this->detectRouteBase();
    }

    protected function detectRouteBase(): ?string
    {
        try {
            $currentRouteName = \Route::currentRouteName();
            if (!$currentRouteName) {
                return 'home';
            }

            foreach ($this->locales as $locale) {
                if (str_starts_with($currentRouteName, $locale . '.')) {
                    return substr($currentRouteName, strlen($locale) + 1);
                }
            }
            return 'home';
        } catch (\Throwable $e) {
            return 'home';
        }
    }

    public function render()
    {
        return view('filament-i18n::components.language-switcher');
    }
}
