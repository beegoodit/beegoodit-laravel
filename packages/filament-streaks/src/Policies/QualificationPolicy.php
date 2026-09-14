<?php

namespace BeegoodIT\FilamentStreaks\Policies;

use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Policies\Concerns\AuthorizesAdminRecords;
use Illuminate\Contracts\Auth\Authenticatable;

class QualificationPolicy
{
    use AuthorizesAdminRecords;

    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Authenticatable $user, Qualification $qualification): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Authenticatable $user, Qualification $qualification): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(Authenticatable $user, Qualification $qualification): bool
    {
        return $this->isAdmin($user);
    }
}
