<?php

namespace BeegoodIT\FilamentStreaks\Policies;

use BeegoodIT\FilamentStreaks\Models\OperationRun;
use BeegoodIT\FilamentStreaks\Policies\Concerns\AuthorizesAdminRecords;
use Illuminate\Contracts\Auth\Authenticatable;

class OperationRunPolicy
{
    use AuthorizesAdminRecords;

    public function viewAny(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Authenticatable $user, OperationRun $operationRun): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Authenticatable $user, OperationRun $operationRun): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, OperationRun $operationRun): bool
    {
        return $this->isAdmin($user);
    }
}
