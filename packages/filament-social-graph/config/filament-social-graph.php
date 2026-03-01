<?php

return [
    /*
     * Tenancy configuration. When enabled, feed items and subscriptions are scoped by team.
     */
    'tenancy' => [
        'enabled' => env('FILAMENT_SOCIAL_GRAPH_TENANCY', true),
        'team_resolver' => null, // Closure to resolve current team, or null to use Filament::getTenant()
        'team_model' => \App\Models\Team::class,
    ],

    /*
     * Whether Filament resources (FeedItem, Subscription) are registered.
     */
    'resources' => [
        'enabled' => true,
    ],

    /*
     * Models that can act as actors (post feed items, subscribe).
     * Example: [\App\Models\User::class, \App\Models\Team::class]
     */
    'actor_models' => [],

    /*
     * Models that can have entity feeds (feeds scoped to an entity like a project, team).
     * Example: [\App\Models\Team::class, \App\Models\Project::class]
     */
    'entity_models' => [],

    /*
     * Feed page configuration for the public/home feed.
     */
    'feed_page' => [
        'layout' => 'filament-social-graph::layouts.app',
        'show_composer' => true,
        'show_composer_on_entity_feed' => true,
        'actor_feed_url_resolver' => null, // Closure(actor) => url, or null for default
    ],

    /*
     * Attachment upload configuration.
     */
    'attachments' => [
        'max_file_size' => env('FILAMENT_SOCIAL_GRAPH_MAX_FILE_SIZE', 10240), // KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],
    ],
];
