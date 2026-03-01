<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class DenyFeedItemPolicy
{
    public function create(?Authenticatable $user, ?Model $entity = null): bool
    {
        return false;
    }
}
