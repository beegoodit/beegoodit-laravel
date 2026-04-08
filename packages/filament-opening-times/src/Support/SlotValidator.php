<?php

namespace BeegoodIT\FilamentOpeningTimes\Support;

use InvalidArgumentException;

final class SlotValidator
{
    /**
     * @param  array<int, array{day_of_week: int, opens_at: string, closes_at: string}>  $slots
     */
    public static function validate(array $slots): void
    {
        /** @var array<int, list<array{0: int, 1: int}>> $halfOpenByDay minute ranges [start, end) per weekday 0–6 */
        $halfOpenByDay = array_fill(0, 7, []);

        foreach ($slots as $index => $slot) {
            $day = (int) $slot['day_of_week'];
            if ($day < 0 || $day > 6) {
                throw new InvalidArgumentException("Invalid day_of_week at index {$index}.");
            }
            $openM = self::timeToMinutes($slot['opens_at']);
            $closeM = self::timeToMinutes($slot['closes_at']);

            if ($openM === $closeM && $slot['opens_at'] === $slot['closes_at']) {
                throw new InvalidArgumentException("opens_at and closes_at must differ at index {$index}.");
            }

            if ($closeM > $openM) {
                $halfOpenByDay[$day][] = [$openM, $closeM];
            } else {
                $halfOpenByDay[$day][] = [$openM, 24 * 60];
                $nextDay = ($day + 1) % 7;
                $halfOpenByDay[$nextDay][] = [0, $closeM];
            }
        }

        for ($d = 0; $d < 7; $d++) {
            self::assertHalfOpenNonOverlapping($halfOpenByDay[$d], $d);
        }
    }

    /**
     * @param  list<array{0: int, 1: int}>  $intervals
     */
    private static function assertHalfOpenNonOverlapping(array $intervals, int $day): void
    {
        if ($intervals === []) {
            return;
        }
        usort($intervals, fn (array $a, array $b): int => $a[0] <=> $b[0]);
        $end = $intervals[0][1];
        for ($i = 1, $c = count($intervals); $i < $c; $i++) {
            if ($intervals[$i][0] < $end) {
                throw new InvalidArgumentException("Overlapping slots on weekday {$day}.");
            }
            $end = max($end, $intervals[$i][1]);
        }
    }

    private static function timeToMinutes(string $time): int
    {
        $time = trim($time);
        $parts = explode(':', $time);
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);
        $s = (int) ($parts[2] ?? 0);

        return min(24 * 60, $h * 60 + $m + (int) floor($s / 60));
    }
}
