<?php

namespace BeegoodIT\FilamentI18n;

use Closure;

/**
 * Helper class for internationalization utilities.
 */
class I18nHelper
{
    /**
     * Get the list of available locale codes.
     *
     * @return array<string>
     */
    public function availableLocales(): array
    {
        return config('filament-i18n.available_locales', ['en']);
    }

    /**
     * Get locale options for form selects/radios.
     * Returns an array of locale code => native name.
     *
     * @return array<string, string>
     */
    public function localeOptions(): array
    {
        $available = $this->availableLocales();
        $allLocales = config('filament-i18n.locales', []);
        $options = [];

        foreach ($available as $locale) {
            if (isset($allLocales[$locale])) {
                $options[$locale] = $allLocales[$locale]['native'];
            } else {
                // Fallback to locale code if not in metadata
                $options[$locale] = strtoupper($locale);
            }
        }

        return $options;
    }

    /**
     * Get locale options with flags for display.
     * Returns an array of locale code => "flag native_name".
     *
     * @return array<string, string>
     */
    public function localeOptionsWithFlags(): array
    {
        $available = $this->availableLocales();
        $allLocales = config('filament-i18n.locales', []);
        $options = [];

        foreach ($available as $locale) {
            if (isset($allLocales[$locale])) {
                $meta = $allLocales[$locale];
                $options[$locale] = $meta['flag'] . ' ' . $meta['native'];
            } else {
                $options[$locale] = strtoupper($locale);
            }
        }

        return $options;
    }

    /**
     * Get full metadata for a specific locale.
     *
     * @return array{native: string, flag: string, rtl: bool}|null
     */
    public function localeMetadata(string $locale): ?array
    {
        return config("filament-i18n.locales.{$locale}");
    }

    /**
     * Check if a locale code is valid (in available locales).
     */
    public function isValidLocale(string $locale): bool
    {
        return in_array($locale, $this->availableLocales(), true);
    }

    /**
     * Check if a locale is right-to-left.
     */
    public function isRtl(string $locale): bool
    {
        $meta = $this->localeMetadata($locale);

        return $meta['rtl'] ?? false;
    }

    /**
     * Get the native name for a locale.
     */
    public function nativeName(string $locale): string
    {
        $meta = $this->localeMetadata($locale);

        return $meta['native'] ?? strtoupper($locale);
    }

    /**
     * Get the flag emoji for a locale.
     */
    public function flag(string $locale): string
    {
        $meta = $this->localeMetadata($locale);

        return $meta['flag'] ?? '';
    }

    public function withLocale(string $locale, Closure $callback): void
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);
        $callback();
        app()->setLocale($previousLocale);
    }
}
