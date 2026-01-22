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

    'auto_assign_teams' => env('OAUTH_AUTO_ASSIGN_TEAMS', false),

    /*
    |--------------------------------------------------------------------------
    | Sync Avatars
    |--------------------------------------------------------------------------
    |
    | When enabled, profile pictures from OAuth providers will be downloaded
    | and synced to the user model.
    |
    */

    'sync_avatars' => env('OAUTH_SYNC_AVATARS', false),

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
    | Configuration for supported OAuth providers. Currently supports Microsoft and Discord.
    |
    */

    'providers' => [
        'microsoft' => [
            'enabled' => env('OAUTH_MICROSOFT_ENABLED', false),
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
            'tenant_id' => env('MICROSOFT_TENANT_ID', 'common'),
            'redirect' => env('APP_URL') . '/me/oauth/callbaccrosoft',
            'team_assignment' => env('OAUTH_MICROSOFT_TEAM_ASSIGNMENT', true),
        ],
        'discord' => [
            'enabled' => env('OAUTH_DISCORD_ENABLED', false),
            'client_id' => env('DISCORD_CLIENT_ID'),
            'client_secret' => env('DISCORD_CLIENT_SECRET'),
            'redirect' => env('APP_URL') . '/me/oauth/callback/discord',
            'team_assignment' => env('OAUTH_DISCORD_TEAM_ASSIGNMENT', false),
        ],
    ],

];
