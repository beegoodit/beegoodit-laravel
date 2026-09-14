<?php

namespace BeegoodIT\FilamentStreaks\Policies;

use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Policies\Concerns\AuthorizesAdminRecords;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationPolicy
{
    use AuthorizesAdminRecords;

    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Authenticatable $user, Notification $notification): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Authenticatable $user, Notification $notification): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(Authenticatable $user, Notification $notification): bool
    {
        return $this->isAdmin($user);
    }
}
