<?php

namespace BeeGoodIT\FilamentUserAvatar\Models\Concerns;

use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;

trait HasAvatar
{
    use HasStoredFiles;

    /**
     * Get the public URL to the user's avatar, or null if none.
     * Uses the HasStoredFiles trait's getFileUrl method.
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getFileUrl($this->avatar ?? null);
    }

    /**
     * Get the avatar URL for Filament (used in portal navbar).
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getAvatarUrl();
    }

    /**
     * Get the user's initials for fallback display.
     */
    public function initials(): string
    {
        return \Illuminate\Support\Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
            ->implode('');
    }
}

