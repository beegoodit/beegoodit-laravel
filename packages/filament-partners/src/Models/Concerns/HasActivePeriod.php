<?php

namespace BeegoodIT\FilamentPartners\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasActivePeriod
{
    public function scopeActive(Builder $query, \DateTimeInterface|string|null $at = null): Builder
    {
        $at = $at instanceof \DateTimeInterface
            ? Carbon::instance($at)
            : ($at ? Carbon::parse($at) : now());

        return $query->where('active_from', '<=', $at)->where('active_to', '>=', $at);
    }

    public function activeAt(\DateTimeInterface|string|null $timestamp = null): bool
    {
        $at = $timestamp instanceof \DateTimeInterface
            ? Carbon::instance($timestamp)
            : ($timestamp ? Carbon::parse($timestamp) : now());

        return $this->active_from <= $at && $this->active_to >= $at;
    }
}
