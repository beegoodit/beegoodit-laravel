<?php

namespace BeeGoodIT\FilamentI18n\Tests\Unit;

use BeeGoodIT\FilamentI18n\Facades\FilamentI18n;
use BeeGoodIT\FilamentI18n\I18nHelper;
use BeeGoodIT\FilamentI18n\Tests\TestCase;

class FilamentI18nTest extends TestCase
{
    public function test_available_locales_returns_config_value(): void
    {
        config(['filament-i18n.available_locales' => ['en', 'de']]);

        $helper = new I18nHelper;

        $this->assertEquals(['en', 'de'], $helper->availableLocales());
    }

    public function test_locale_options_returns_native_names(): void
    {
        config([
            'filament-i18n.available_locales' => ['en', 'de'],
            'filament-i18n.locales' => [
                'en' => ['native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
                'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertEquals([
            'en' => 'English',
            'de' => 'Deutsch',
        ], $helper->localeOptions());
    }

    public function test_locale_options_with_flags_includes_emoji(): void
    {
        config([
            'filament-i18n.available_locales' => ['en', 'de'],
            'filament-i18n.locales' => [
                'en' => ['native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
                'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertEquals([
            'en' => '🇬🇧 English',
            'de' => '🇩🇪 Deutsch',
        ], $helper->localeOptionsWithFlags());
    }

    public function test_locale_metadata_returns_full_data(): void
    {
        config([
            'filament-i18n.locales' => [
                'ar' => ['native' => 'العربية', 'flag' => '🇸🇦', 'rtl' => true],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertEquals([
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'rtl' => true,
        ], $helper->localeMetadata('ar'));
    }

    public function test_is_valid_locale_returns_true_for_available_locale(): void
    {
        config(['filament-i18n.available_locales' => ['en', 'de', 'es']]);

        $helper = new I18nHelper;

        $this->assertTrue($helper->isValidLocale('de'));
        $this->assertFalse($helper->isValidLocale('fr'));
    }

    public function test_is_rtl_returns_correct_value(): void
    {
        config([
            'filament-i18n.locales' => [
                'en' => ['native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
                'ar' => ['native' => 'العربية', 'flag' => '🇸🇦', 'rtl' => true],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertFalse($helper->isRtl('en'));
        $this->assertTrue($helper->isRtl('ar'));
    }

    public function test_native_name_returns_name(): void
    {
        config([
            'filament-i18n.locales' => [
                'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertEquals('Deutsch', $helper->nativeName('de'));
    }

    public function test_flag_returns_emoji(): void
    {
        config([
            'filament-i18n.locales' => [
                'es' => ['native' => 'Español', 'flag' => '🇪🇸', 'rtl' => false],
            ],
        ]);

        $helper = new I18nHelper;

        $this->assertEquals('🇪🇸', $helper->flag('es'));
    }

    public function test_fallback_for_unknown_locale(): void
    {
        config([
            'filament-i18n.available_locales' => ['xx'],
            'filament-i18n.locales' => [],
        ]);

        $helper = new I18nHelper;

        // Unknown locales fall back to uppercase code
        $this->assertEquals(['xx' => 'XX'], $helper->localeOptions());
        $this->assertEquals('XX', $helper->nativeName('xx'));
        $this->assertEquals('', $helper->flag('xx'));
        $this->assertFalse($helper->isRtl('xx'));
    }

    public function test_facade_works(): void
    {
        config(['filament-i18n.available_locales' => ['en', 'de']]);

        $this->assertEquals(['en', 'de'], FilamentI18n::availableLocales());
    }
}
