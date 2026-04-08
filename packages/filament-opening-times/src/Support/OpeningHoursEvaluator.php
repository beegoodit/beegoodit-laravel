<?php

namespace BeegoodIT\FilamentOpeningTimes\Support;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Models\Slot;
use Carbon\CarbonInterface;

final class OpeningHoursEvaluator
{
    public static function isOpen(Schedule $schedule, CarbonInterface $at): bool
    {
        if (! $schedule->isActiveAt($at)) {
            return false;
        }

        $schedule->loadMissing('slots');

        $local = $at->copy()->timezone($schedule->timezone);
        $dow = (int) $local->format('w');
        $minute = (int) $local->format('G') * 60 + (int) $local->format('i');

        foreach ($schedule->slots as $slot) {
            if ((int) $slot->day_of_week !== $dow) {
                continue;
            }
            if (self::sameDayPortionCovers($slot, $minute)) {
                return true;
            }
        }

        $prevDow = ($dow + 6) % 7;
        foreach ($schedule->slots as $slot) {
            if ((int) $slot->day_of_week !== $prevDow) {
                continue;
            }
            if (self::overnightMorningCovers($slot, $minute)) {
                return true;
            }
        }

        return false;
    }

    private static function slotOpenCloseMinutes(Slot $slot): array
    {
        $o = $slot->opens_at;
        $c = $slot->closes_at;

        $openM = $o instanceof CarbonInterface ? $o->hour * 60 + $o->minute : 0;
        $closeM = $c instanceof CarbonInterface ? $c->hour * 60 + $c->minute : 0;

        return [$openM, $closeM];
    }

    /**
     * Half-open [open, close) for same calendar day; overnight evening uses [open, 1440).
     */
    private static function sameDayPortionCovers(Slot $slot, int $minuteOfDay): bool
    {
        [$openM, $closeM] = self::slotOpenCloseMinutes($slot);

        if ($closeM > $openM) {
            return $minuteOfDay >= $openM && $minuteOfDay < $closeM;
        }

        return $minuteOfDay >= $openM && $minuteOfDay < 24 * 60;
    }

    private static function overnightMorningCovers(Slot $slot, int $minuteOfDay): bool
    {
        [$openM, $closeM] = self::slotOpenCloseMinutes($slot);

        if ($closeM <= $openM) {
            return $minuteOfDay >= 0 && $minuteOfDay < $closeM;
        }

        return false;
    }
}
