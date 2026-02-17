<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Concerns;

use BeegoodIT\FilamentTimeline\Services\TimelineAggregator;
use Illuminate\Support\Collection;

trait HasTimeline
{
    /**
     * @return Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>
     */
    public function getTimeline(): Collection
    {
        return resolve(TimelineAggregator::class)->for($this);
    }
}
