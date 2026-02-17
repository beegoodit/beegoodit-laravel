<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Contracts;

use BeegoodIT\FilamentTimeline\Data\TimelineEntry;

interface ProvidesTimelineEvent
{
    public function toTimelineEvent(): TimelineEntry;
}
