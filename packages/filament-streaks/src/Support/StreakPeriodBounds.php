<?php

namespace BeegoodIT\FilamentStreaks\Support;

use BeegoodIT\FilamentStreaks\Contracts\PeriodicityContract;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StreakPeriodBounds
{
    /**
     * Cached run end: last day of the last period that has ≥1 qualification (subject TZ).
     * Falls back to the period containing start_at when the run has no qualifications.
     */
    public function lastQualifiedPeriodEndDate(Streak $streak, PeriodicityContract $periodicity, string $timezone): string
    {
        $qualifications = $streak->relationLoaded('qualifications')
            ? $streak->qualifications
            : $streak->qualifications()->get(['happened_at']);

        if ($qualifications->isEmpty()) {
            $period = $periodicity->periodContaining(
                Carbon::parse($streak->start_at, $timezone)->startOfDay(),
                $timezone,
            );

            return Carbon::parse($period['end'])->timezone($timezone)->toDateString();
        }

        $latest = $qualifications
            ->sortByDesc(fn ($qualification): int => Carbon::parse($qualification->happened_at)->getTimestamp())
            ->first();

        $period = $periodicity->periodContaining(Carbon::parse($latest->happened_at), $timezone);

        return Carbon::parse($period['end'])->timezone($timezone)->toDateString();
    }

    /**
     * True when there is at least one period strictly between the last qualified period
     * (or start_at period) and the event period — i.e. a gap that should close the run.
     *
     * @param  Collection<int, object{happened_at: mixed}>  $qualifications
     */
    public function hasGapBeforeEvent(
        PeriodicityContract $periodicity,
        string $timezone,
        string $startAt,
        Collection $qualifications,
        Carbon $eventAt,
    ): bool {
        $anchor = $qualifications->isEmpty()
            ? Carbon::parse($startAt, $timezone)->startOfDay()
            : Carbon::parse(
                $qualifications
                    ->sortByDesc(fn ($qualification): int => Carbon::parse($qualification->happened_at)->getTimestamp())
                    ->first()
                    ->happened_at
            );

        $lastPeriod = $periodicity->periodContaining($anchor, $timezone);
        $eventPeriod = $periodicity->periodContaining($eventAt, $timezone);

        if ($lastPeriod['key'] === $eventPeriod['key']) {
            return false;
        }

        $from = Carbon::parse($lastPeriod['end'])->timezone($timezone)->addDay()->startOfDay();
        $to = Carbon::parse($eventPeriod['start'])->timezone($timezone)->subDay()->endOfDay();

        if ($from->gt($to)) {
            return false;
        }

        return $periodicity->periodsBetween($from, $to, $timezone)->isNotEmpty();
    }
}
