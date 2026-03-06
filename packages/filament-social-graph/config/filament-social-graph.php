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
     * Attachment limits for feed item create/edit (public forms).
     */
    'attachments' => [
        'max_files' => 5,
        'max_file_size_kb' => 5120,
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
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
        'actor_feed_url_resolver' => null, // Closure(actor) => url, or null for default
        'feed_item_edit_url_resolver' => null, // Closure(FeedItem $feedItem): ?string, or null to hide edit link
        'feed_item_destroy_url_resolver' => null, // Closure(FeedItem $feedItem): ?string, or null to hide delete
        'edit_view' => null, // App view name for GET feed edit (e.g. livewire.public-team-feed-edit); null = package feed.edit
    ],

];
