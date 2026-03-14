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
        if (! $user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            return false;
        }

        if ($entity === null) {
            return true;
        }

        if (! $entity instanceof Model) {
            return false;
        }

        $ownerModels = config('filament-social-graph.owner_models', []);

        return in_array($entity->getMorphClass(), $ownerModels, true);
    }

    /**
     * Determine whether the user can update the feed item.
     */
    public function update(?Authenticatable $user, FeedItem $feedItem): bool
    {
        if (! $user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            return false;
        }

        $owner = $feedItem->owner;
        if ($owner === null) {
            return true;
        }

        $ownerModels = config('filament-social-graph.owner_models', []);

        return in_array($owner->getMorphClass(), $ownerModels, true);
    }

    /**
     * Determine whether the user can delete the feed item.
     */
    public function delete(?Authenticatable $user, FeedItem $feedItem): bool
    {
        return $this->update($user, $feedItem);
    }
}
