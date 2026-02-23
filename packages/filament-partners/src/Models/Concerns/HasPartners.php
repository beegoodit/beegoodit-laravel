<?php

namespace BeegoodIT\FilamentPartners\Models\Concerns;

use BeegoodIT\FilamentPartners\Models\Partner;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPartners
{
    /**
     * Partners (e.g. sponsors) owned by this model (team, tour, etc.).
     */
    public function partners(): MorphMany
    {
        return $this->morphMany(Partner::class, 'partnerable');
    }
}
