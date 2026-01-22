<?php

namespace BeegoodIT\FilamentI18n\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array availableLocales()
 * @method static array localeOptions()
 * @method static array localeOptionsWithFlags()
 * @method static array|null localeMetadata(string $locale)
 * @method static bool isValidLocale(string $locale)
 * @method static bool isRtl(string $locale)
 * @method static string nativeName(string $locale)
 * @method static string flag(string $locale)
 *
 * @see \BeegoodIT\FilamentI18n\I18nHelper
 */
class FilamentI18n extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-i18n';
    }
}
