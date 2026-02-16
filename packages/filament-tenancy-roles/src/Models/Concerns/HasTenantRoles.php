<?php

namespace BeegoodIT\FilamentTenancyRoles\Models\Concerns;

use BeegoodIT\FilamentTenancyRoles\Models\Membership;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTenantRoles
{
    /**
     * Get all of the users that belong to the team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(config('auth.providers.users.model', \App\Models\User::class))
            ->using(Membership::class)
            ->as('membership')
            ->withPivot('role')
            ->withTimestamps();
    }
}
