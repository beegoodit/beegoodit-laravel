<?php

namespace BeegoodIT\FilamentPartners\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasActivePeriod
{
    protected function scopeActive(Builder $query, \DateTimeInterface|string|null $at = null): Builder
    {
        $at = $at instanceof \DateTimeInterface
            ? \Illuminate\Support\Facades\Date::instance($at)
            : ($at ? \Illuminate\Support\Facades\Date::parse($at) : now());

        return $query->where('active_from', '<=', $at)->where('active_to', '>=', $at);
    }

    public function activeAt(\DateTimeInterface|string|null $timestamp = null): bool
    {
        $at = $timestamp instanceof \DateTimeInterface
            ? \Illuminate\Support\Facades\Date::instance($timestamp)
            : ($timestamp ? \Illuminate\Support\Facades\Date::parse($timestamp) : now());

        return $this->active_from <= $at && $this->active_to >= $at;
    }
}
