<?php

namespace BeegoodIT\FilamentStreaks\Support;

use Carbon\CarbonInterval;
use InvalidArgumentException;

class DurationParser
{
    public function toInterval(string $duration): CarbonInterval
    {
        if (! preg_match('/^(\d+)(m|h|d)$/', $duration, $matches)) {
            throw new InvalidArgumentException("Invalid duration format [{$duration}]. Expected e.g. 5m, 1h, 2d.");
        }

        $value = (int) $matches[1];
        $unit = $matches[2];

        return match ($unit) {
            'm' => CarbonInterval::minutes($value),
            'h' => CarbonInterval::hours($value),
            'd' => CarbonInterval::days($value),
            default => throw new InvalidArgumentException("Unsupported duration unit [{$unit}]."),
        };
    }

    public function toSeconds(string $duration): int
    {
        return (int) $this->toInterval($duration)->totalSeconds;
    }
}
