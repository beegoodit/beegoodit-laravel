<?php

namespace BeegoodIT\FilamentSocialGraph\Policies;

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
}
