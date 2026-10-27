<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | When enabled, users will be able to register through the /register route.
    | This is disabled by default to avoid accidental registration prompts
    | in apps that don't want it.
    |
    */

    'registration' => false,

    /*
    |--------------------------------------------------------------------------
    | Email verification
    |--------------------------------------------------------------------------
    |
    | When enabled, the me panel will require email verification. Users must
    | verify their email after registration before accessing protected routes.
    |
    */

    'email_verification' => false,

    /*
    |--------------------------------------------------------------------------
    | Tenant panel id
    |--------------------------------------------------------------------------
    |
    | Filament panel id for the tenant app (not /me). Used for:
    | “Back to Team Dashboard”, shared vite theme on /me, OAuth redirect
    | route names, and 2FA challenge logout when those routes live on the
    | tenant panel. Default `portal` keeps older hosts working. Octake sets
    | this to `teams`.
    |
    */

    'tenant_panel_id' => 'portal',

    /*
    |--------------------------------------------------------------------------
    | Tenant panel Vite theme
    |--------------------------------------------------------------------------
    |
    | CSS entry shared by /me and the tenant panel. Prefer matching the Filament
    | convention: resources/css/filament/{tenant_panel_id}/theme.css
    |
    */

    'tenant_panel_vite_theme' => 'resources/css/filament/portal/theme.css',

    /*
    |--------------------------------------------------------------------------
    | Configure /me panel
    |--------------------------------------------------------------------------
    |
    | Optional callable receiving the Filament Panel for /me after the package
    | defaults are applied. Host apps can register extra pages, plugins, and
    | resources (e.g. Facts / Connections on Octake).
    |
    | Signature: function (\Filament\Panel $panel): \Filament\Panel
    |
    */

    'configure_panel' => null,

];
