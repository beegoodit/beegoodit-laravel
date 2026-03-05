<?php

namespace BeegoodIT\FilamentSocialGraph\Models\Concerns;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSocialFeed
{
    /**
     * Get feed items posted by this model (actor).
     */
    public function feedItems(): HasMany
    {
        return $this->hasMany(FeedItem::class, 'actor_id')
            ->where('actor_type', $this->getMorphClass())
            ->orderByDesc('created_at');
    }

    /**
     * Create a feed item as this actor.
     *
     * @param  array{subject?: string, body?: string}  $data
     */
    public function createFeedItem(array $data): FeedItem
    {
        $feedItem = new FeedItem([
            'actor_type' => $this->getMorphClass(),
            'actor_id' => $this->getKey(),
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
            \BeegoodIT\FilamentSocialGraph\Models\Subscription::class,
            'feed_owner'
        );
    }
}
