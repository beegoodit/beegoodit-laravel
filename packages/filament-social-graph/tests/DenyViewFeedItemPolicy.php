<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Policy that denies viewing a single feed item (for controller tests).
 */
class DenyViewFeedItemPolicy
{
    public function viewAny(?Authenticatable $user): bool
    {
        return true;
    }

    public function view(?Authenticatable $user, FeedItem $feedItem): bool
    {
        return false;
    }
}
