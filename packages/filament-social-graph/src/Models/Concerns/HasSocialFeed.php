<?php

namespace BeegoodIT\FilamentSocialGraph\Models\Concerns;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSocialFeed
{
    /**
     * Get the feed owned by this model.
     */
    public function feed(): MorphOne
    {
        return $this->morphOne(Feed::class, 'owner');
    }

    /**
     * Get feed items posted to this model's feed.
     */
    public function feedItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            FeedItem::class,
            Feed::class,
            'owner_id',
            'feed_id',
            $this->getKeyName(),
            'id'
        )->where('feeds.owner_type', $this->getMorphClass())
            ->orderByDesc('feed_items.created_at');
    }

    /**
     * Create a feed item as this owner.
     *
     * @param  array{subject?: string, body?: string}  $data
     */
    public function createFeedItem(array $data): FeedItem
    {
        $feed = Feed::firstOrCreateForOwner($this);

        $feedItem = new FeedItem([
            'feed_id' => $feed->getKey(),
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'] ?? null,
        ]);

        if (config('filament-social-graph.tenancy.enabled') && $this->relationLoaded('team')) {
            $feedItem->team_id = $this->team?->getKey();
        }

        $feedItem->save();

        return $feedItem;
    }

    /**
     * Get subscribers to this actor's feed.
     */
    public function subscribers()
    {
        return $this->morphMany(
            \BeegoodIT\FilamentSocialGraph\Models\FeedSubscription::class,
            'feed_owner'
        );
    }
}
