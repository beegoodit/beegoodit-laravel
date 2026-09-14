<?php

namespace BeegoodIT\FilamentStreaks\Policies\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;

trait AuthorizesAdminRecords
{
    protected function isAdmin(Authenticatable $user): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return (bool) ($user->is_admin ?? false);
    }
}
