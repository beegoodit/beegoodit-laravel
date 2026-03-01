<?php

namespace BeegoodIT\FilamentSocialGraph\Enums;

enum Visibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';
    case Followers = 'followers';

    public function label(): string
    {
        return match ($this) {
            self::Public => __('filament-social-graph::feed_item.visibility_public'),
            self::Unlisted => __('filament-social-graph::feed_item.visibility_unlisted'),
            self::Private => __('filament-social-graph::feed_item.visibility_private'),
            self::Followers => __('filament-social-graph::feed_item.visibility_followers'),
        };
    }
}
