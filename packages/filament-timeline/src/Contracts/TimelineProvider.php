<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TimelineProvider
{
    /**
     * @return Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>
     */
    public function discover(Model $subject): Collection;
}
