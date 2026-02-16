<?php

namespace BeegoodIT\FilamentTenancyRoles\Models\Concerns;

use BeegoodIT\FilamentTenancyRoles\Enums\TeamRole;
use BeegoodIT\FilamentTenancyRoles\Models\Membership;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithTenantRoles
{
    /**
     * Get the teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(config('filament-tenancy.model', \App\Models\Team::class))
            ->using(Membership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Determine if the user is an admin of the given team.
     */
    public function isTeamAdmin(Model $team): bool
    {
        if (method_exists($this, 'isAdmin') && $this->isAdmin()) {
            return true;
        }

        return $this->hasTeamRole($team, [TeamRole::Admin, TeamRole::Owner]);
    }

    /**
     * Determine if the user is the owner of the given team.
     */
    public function isTeamOwner(Model $team): bool
    {
        if (method_exists($this, 'isAdmin') && $this->isAdmin()) {
            return true;
        }

        return $this->hasTeamRole($team, TeamRole::Owner);
    }

    /**
     * Determine if the user has the given role on the given team.
     *
     * @param  string|TeamRole|array<string|TeamRole>  $role
     */
    public function hasTeamRole(Model $team, string|TeamRole|array $role): bool
    {
        if (method_exists($this, 'isAdmin') && $this->isAdmin()) {
            return true;
        }

        $roles = is_array($role) ? $role : [$role];

        $roles = array_map(fn (\BeegoodIT\FilamentTenancyRoles\Enums\TeamRole|string $r) => $r instanceof TeamRole ? $r->value : $r, $roles);

        $teamRole = $this->teamRole($team);

        return $teamRole && in_array($teamRole->value, $roles);
    }

    /**
     * Get the user's role on the given team.
     */
    public function teamRole(Model $team): ?TeamRole
    {
        if ($this->relationLoaded('teams')) {
            $membership = $this->teams->firstWhere($team->getKeyName(), $team->getKey())?->pivot;
        } else {
            $membership = $this->teams()->where($team->getTable().'.'.$team->getKeyName(), $team->getKey())->first()?->pivot;
        }

        return $membership?->role;
    }
}
