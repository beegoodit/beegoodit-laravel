<?php

namespace BeegoodIT\FilamentI18n\View\Components;

use Illuminate\View\Component;

/**
 * Flux UI variant of the language switcher. Requires livewire/flux.
 */
class LanguageSwitcherFlux extends Component
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
            if (! $currentRouteName) {
                return 'home';
            }

            foreach ($this->locales as $locale) {
                if (str_starts_with($currentRouteName, $locale.'.')) {
                    return substr($currentRouteName, strlen((string) $locale) + 1);
                }
            }

            return 'home';
        } catch (\Throwable) {
            return 'home';
        }
    }

    /**
     * @return array<string, string> locale => url
     */
    public function localeUrls(): array
    {
        $urls = [];
        foreach ($this->locales as $locale) {
            $routeName = $locale.'.'.($this->routeBase ?? 'home');
            $params = request()->route()?->parameters() ?? [];
            $urls[$locale] = \Route::has($routeName) ? route($routeName, $params) : '/'.$locale;
        }

        return $urls;
    }

    /**
     * @return array<string, string> locale => native name
     */
    public function localeNames(): array
    {
        $names = [];
        foreach ($this->locales as $locale) {
            $names[$locale] = \BeegoodIT\FilamentI18n\Facades\FilamentI18n::nativeName($locale);
        }

        return $names;
    }

    public function render(): mixed
    {
        if (! class_exists(\Flux\Flux::class)) {
            throw new \RuntimeException(
                'The filament-i18n Flux language switcher requires livewire/flux. '.
                'Install it with: composer require livewire/flux'
            );
        }

        return view('filament-i18n::components.language-switcher-flux')->with([
            'localeUrlMap' => $this->localeUrls(),
            'localeNameMap' => $this->localeNames(),
        ]);
    }
}
