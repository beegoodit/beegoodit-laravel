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
     * Whether Filament resources (FeedItem, FeedSubscription, FeedSubscriptionRule) are registered.
     */
    'resources' => [
        'enabled' => true,
    ],

    /*
     * Models that can act as feed owners (post feed items, subscribe).
     * Example: [\App\Models\User::class, \App\Models\Team::class]
     */
    'owner_models' => [],

    /*
     * Models that can have entity feeds (feeds scoped to an entity like a project, team).
     * Example: [\App\Models\Team::class, \App\Models\Project::class]
     */
    'entity_models' => [],

    /*
     * Allowed scope values and labels for subscription rules.
     * Apps can add scope values (e.g. 'tour_members' => 'Tour members') in published config.
     */
    'subscription_rule_scopes' => [
        'all_users' => 'All users',
        'team_members' => 'Team members',
    ],

    /*
     * Resolvers that return subscribers (models using HasSocialSubscriptions) for each scope.
     * Key = scope value (e.g. 'all_users'), value = [ClassName::class, 'method'] (callable, serializable for config:cache).
     * Method signature: (FeedSubscriptionRule $rule): iterable<Model>. Do not use closures (non-serializable).
     */
    'subscription_rule_scope_resolver' => [],

    /*
     * Attachment limits for feed item create/edit (public forms).
     */
    'attachments' => [
        'max_files' => 5,
        'max_file_size_kb' => 5120,
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
        'multiple_upload_mode' => 'auto', // 'auto' | 'native' | 'single_per_request'
        'signed_url_ttl_minutes' => 60,   // TTL for temporary (signed) URLs when using private S3/Spaces
        'thumbnails' => [
            'width' => 400,
            'height' => 400,
            'quality' => 85,
        ],
    ],

    /*
     * Feed page configuration for the public/home feed.
     */
    'feed_page' => [
        'layout' => 'filament-social-graph::layouts.app',
        'index_view' => null, // App view name to use for GET feed (e.g. livewire.public-team-feed) for breadcrumb/wrapper; null = package feed.index
        'show_composer' => true,
        'show_composer_on_entity_feed' => true,
        'authorize_create_ability' => 'create', // Ability name for Gate (composer form visibility + submit auth)
        'authorize_update_ability' => 'update',
        'authorize_delete_ability' => 'delete',
        'owner_feed_url_resolver' => null, // Closure(owner) => url, or null for default
        'edit_view' => null, // App view name for GET feed edit (e.g. livewire.public-team-feed-edit); null = package feed.edit
    ],

];
