<?php

namespace BeegoodIT\FilamentOpeningTimes\Support;

use Carbon\CarbonInterface;

final class ScheduleWindowOverlap
{
    /**
     * Two [start, end] windows overlap (strict; touching endpoints do not overlap).
     */
    public static function rangesOverlap(
        CarbonInterface $newStart,
        CarbonInterface $newEnd,
        CarbonInterface $existingStart,
        CarbonInterface $existingEnd,
    ): bool {
        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }
}
