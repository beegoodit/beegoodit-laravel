<?php

namespace BeegoodIT\FilamentSocialGraph\Policies;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class FeedItemPolicy
{
    /**
     * Determine whether the user can create a feed item (for the given entity feed, or global feed when entity is null).
     */
    public function create(?Authenticatable $user, mixed $entity = null): bool
    {
        if ($user === null) {
            return false;
        }

        if ($entity === null) {
            return true;
        }

        if (! $entity instanceof Model) {
            return false;
        }

        $actorModels = config('filament-social-graph.actor_models', []);

        return in_array($entity->getMorphClass(), $actorModels, true);
    }

    /**
     * Determine whether the user can update the feed item.
     */
    public function update(?Authenticatable $user, FeedItem $feedItem): bool
    {
        if ($user === null) {
            return false;
        }

        $actor = $feedItem->actor;
        if ($actor === null) {
            return true;
        }

        $actorModels = config('filament-social-graph.actor_models', []);

        return in_array($actor->getMorphClass(), $actorModels, true);
    }

    /**
     * Determine whether the user can delete the feed item.
     */
    public function delete(?Authenticatable $user, FeedItem $feedItem): bool
    {
        return $this->update($user, $feedItem);
    }
}
