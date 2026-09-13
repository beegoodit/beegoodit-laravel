<?php

return [
    /*
     * Enable or disable the cookie consent banner
     */
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),

    /*
     * Cookie name for storing user's consent
     */
    'cookie_name' => '__cookie_consent',

    /*
     * Cookie lifetime in days
     */
    'cookie_lifetime' => 365,

    /*
     * Position of the banner: 'bottom' or 'top'
     */
    'position' => 'bottom',

    /*
     * Blade view for the Livewire component.
     * Zinc utilities (Octake): cookie-consent::livewire.cookie-consent
     * daisyUI (TallCMS): cookie-consent::livewire.cookie-consent-daisy
     * Set this in published config (not .env). TallCMS glue hardcodes daisy.
     */
    'view' => 'cookie-consent::livewire.cookie-consent',

    /*
     * Cookie policy URLs (per locale). Empty string hides the “learn more” link.
     */
    'policy_url_en' => env('COOKIE_POLICY_URL_EN', ''),
    'policy_url_de' => env('COOKIE_POLICY_URL_DE', ''),

    /*
     * GTM event name triggered when consent changes
     */
    'gtm_event' => 'cookie_refresh',

    /*
     * Paths to ignore (banner won't show). Laravel Request::is() patterns.
     */
    'ignored_paths' => [
        'admin',
        'admin/*',
        'livewire/*',
        'install',
        'install/*',
        'installer',
        'installer/*',
    ],

    /*
     * Secure cookie setting (HTTPS only)
     */
    'cookie_secure' => env('COOKIE_CONSENT_SECURE', true),
];
