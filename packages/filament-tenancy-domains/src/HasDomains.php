<?php

namespace BeegoodIT\FilamentTenancyDomains;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasDomains
{
    public function domains(): MorphMany
    {
        return $this->morphMany(Domain::class, 'model');
    }

    public function primaryDomain(): MorphOne
    {
        return $this->morphOne(Domain::class, 'model')->where('is_primary', true);
    }

    protected function getPrimaryUrlAttribute(): string
    {
        $domain = $this->primaryDomain?->domain ?? $this->slug; // Fallback to slug if no domain assigned

        $protocol = request()->secure() ? 'https://' : 'http://';

        return $protocol.$domain;
    }
}
