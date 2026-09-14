<?php

namespace BeegoodIT\FilamentStreaks\Policies;

use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Policies\Concerns\AuthorizesAdminRecords;
use Illuminate\Contracts\Auth\Authenticatable;

class StreakPolicy
{
    use AuthorizesAdminRecords;

    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Authenticatable $user, Streak $streak): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Authenticatable $user, Streak $streak): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(Authenticatable $user, Streak $streak): bool
    {
        return $this->isAdmin($user);
    }
}
