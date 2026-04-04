<?php

namespace BeegoodIT\FilamentEntryLinks\Policies;

use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use Illuminate\Foundation\Auth\User;

class EntryLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, EntryLink $entryLink): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, EntryLink $entryLink): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, EntryLink $entryLink): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, EntryLink $entryLink): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, EntryLink $entryLink): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return (bool) ($user->is_admin ?? false);
    }
}
