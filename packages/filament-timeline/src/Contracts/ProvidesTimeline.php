<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Contracts;

use Illuminate\Support\Collection;

interface ProvidesTimeline
{
    /**
     * @return Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>
     */
    public function toTimeline(): Collection;
}
