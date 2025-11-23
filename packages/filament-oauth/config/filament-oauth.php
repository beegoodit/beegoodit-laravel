<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Suppress Installation Instructions
    |--------------------------------------------------------------------------
    |
    | Set to true to hide migration instructions during package operations.
    |
    */

    'suppress_instructions' => env('OAUTH_SUPPRESS_INSTRUCTIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Auto Assign Teams
    |--------------------------------------------------------------------------
    |
    | When enabled, users will automatically be assigned to teams based on
    | their OAuth tenant ID after registration or connection.
    |
    */

    'auto_assign_teams' => env('OAUTH_AUTO_ASSIGN_TEAMS', true),

    /*
    |--------------------------------------------------------------------------
    | Team Model
    |--------------------------------------------------------------------------
    |
    | The model used for teams in your application. This should implement
    | the Filament HasTenants contract.
    |
    */

    'team_model' => env('OAUTH_TEAM_MODEL', \App\Models\Team::class),

    /*
    |--------------------------------------------------------------------------
    | OAuth Providers
    |--------------------------------------------------------------------------
    |
    | Configuration for supported OAuth providers. Currently supports Microsoft.
    |
    */

    'providers' => [
        'microsoft' => [
            'enabled' => env('OAUTH_MICROSOFT_ENABLED', true),
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
            'tenant_id' => env('MICROSOFT_TENANT_ID', 'common'),
            'redirect' => env('APP_URL').'/portal/oauth/callback/microsoft',
        ],
    ],

];
