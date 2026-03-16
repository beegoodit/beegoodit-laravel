<?php

namespace BeegoodIT\FilamentSocialGraph\Models\Concerns;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

trait HasSocialSubscriptions
{
    /**
     * Get subscriptions where this model is the subscriber.
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(FeedSubscription::class, 'subscriber');
    }

    /**
     * Subscribe to another actor's feed.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $feedOwner
     */
    public function subscribeTo($feedOwner): FeedSubscription
    {
        $match = [
            'subscriber_type' => $this->getMorphClass(),
            'subscriber_id' => $this->getKey(),
            'feed_owner_type' => $feedOwner->getMorphClass(),
            'feed_owner_id' => $feedOwner->getKey(),
        ];

        $additional = [];
        if (config('filament-social-graph.tenancy.enabled') && Schema::hasColumn((new FeedSubscription)->getTable(), 'team_id')) {
            $teamId = $this->resolveTeamIdForSubscription($feedOwner);
            if ($teamId !== null) {
                $additional['team_id'] = $teamId;
            }
        }

        return FeedSubscription::firstOrCreate($match, $additional);
    }

    /**
     * Unsubscribe from another actor's feed.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $feedOwner
     */
    public function unsubscribeFrom($feedOwner): bool
    {
        return (bool) FeedSubscription::query()
            ->where('subscriber_type', $this->getMorphClass())
            ->where('subscriber_id', $this->getKey())
            ->where('feed_owner_type', $feedOwner->getMorphClass())
            ->where('feed_owner_id', $feedOwner->getKey())
            ->delete();
    }

    /**
     * Check if this actor is subscribed to another's feed.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $feedOwner
     */
    public function isSubscribedTo($feedOwner): bool
    {
        return FeedSubscription::query()
            ->where('subscriber_type', $this->getMorphClass())
            ->where('subscriber_id', $this->getKey())
            ->where('feed_owner_type', $feedOwner->getMorphClass())
            ->where('feed_owner_id', $feedOwner->getKey())
            ->exists();
    }

    /**
     * Get the home feed (items from subscribed feed owners).
     */
    public function getHomeFeed(int $limit = 20): Collection
    {
        $subscriptions = $this->subscriptions()->get(['feed_owner_type', 'feed_owner_id']);

        if ($subscriptions->isEmpty()) {
            return collect();
        }

        $feedIds = Feed::query()
            ->where(function (Builder $q) use ($subscriptions): void {
                foreach ($subscriptions as $sub) {
                    $q->orWhere(function (Builder $inner) use ($sub): void {
                        $inner->where('owner_type', $sub->feed_owner_type)
                            ->where('owner_id', $sub->feed_owner_id);
                    });
                }
            })
            ->pluck('id');

        if ($feedIds->isEmpty()) {
            return collect();
        }

        $query = FeedItem::query()
            ->with(['feed.owner'])
            ->whereIn('feed_id', $feedIds)
            ->latest()
            ->limit($limit);

        if (config('filament-social-graph.tenancy.enabled') && $this->relationLoaded('team')) {
            $query->where('team_id', $this->team?->getKey());
        }

        return $query->get();
    }

    protected function resolveTeamIdForSubscription($feedOwner): ?string
    {
        if (! config('filament-social-graph.tenancy.enabled')) {
            return null;
        }

        $resolver = config('filament-social-graph.tenancy.team_resolver');
        if ($resolver !== null && is_callable($resolver)) {
            return $resolver($this);
        }

        return $this->team_id ?? $this->team?->getKey() ?? null;
    }
}
