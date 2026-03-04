<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class DenyFeedItemPolicy
{
    public function create(?Authenticatable $user, ?Model $entity = null): bool
    {
        return false;
    }

    public function update(?Authenticatable $user, FeedItem $feedItem): bool
    {
        return false;
    }

    public function delete(?Authenticatable $user, FeedItem $feedItem): bool
    {
        return false;
    }
}
