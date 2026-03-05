<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | List of locale codes that are available in your application.
    | Can be set via APP_AVAILABLE_LOCALES env variable (comma-separated).
    |
    */
    'available_locales' => env('APP_AVAILABLE_LOCALES')
        ? explode(',', env('APP_AVAILABLE_LOCALES'))
        : ['en', 'de', 'es'],

    /*
    |--------------------------------------------------------------------------
    | Locale Metadata
    |--------------------------------------------------------------------------
    |
    | Full metadata for each supported locale. Add entries here for any
    | locales you want to support. The consuming app can extend this by
    | publishing and modifying this config.
    |
    | Each locale entry contains:
    | - native: The locale name in its native language
    | - flag: Emoji flag for visual display
    | - rtl: Whether the language is right-to-left
    |
    */
    'locales' => [
        'en' => ['native' => 'English', 'flag' => '🇬🇧', 'rtl' => false],
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false],
        'es' => ['native' => 'Español', 'flag' => '🇪🇸', 'rtl' => false],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷', 'rtl' => false],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹', 'rtl' => false],
        'pt' => ['native' => 'Português', 'flag' => '🇵🇹', 'rtl' => false],
        'nl' => ['native' => 'Nederlands', 'flag' => '🇳🇱', 'rtl' => false],
        'pl' => ['native' => 'Polski', 'flag' => '🇵🇱', 'rtl' => false],
        'ru' => ['native' => 'Русский', 'flag' => '🇷🇺', 'rtl' => false],
        'zh' => ['native' => '中文', 'flag' => '🇨🇳', 'rtl' => false],
        'ja' => ['native' => '日本語', 'flag' => '🇯🇵', 'rtl' => false],
        'ko' => ['native' => '한국어', 'flag' => '🇰🇷', 'rtl' => false],
        'ar' => ['native' => 'العربية', 'flag' => '🇸🇦', 'rtl' => true],
        'he' => ['native' => 'עברית', 'flag' => '🇮🇱', 'rtl' => true],
        'tr' => ['native' => 'Türkçe', 'flag' => '🇹🇷', 'rtl' => false],
    ],
];
