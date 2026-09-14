<?php

namespace BeegoodIT\FilamentStreaks\Strategies;

use BeegoodIT\FilamentStreaks\Contracts\PeriodicityContract;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsoWeekStrategy implements PeriodicityContract
{
    /**
     * @return array{key: string, start: CarbonInterface, end: CarbonInterface}
     */
    public function periodContaining(CarbonInterface $instant, string $timezone): array
    {
        $local = Carbon::parse($instant)->timezone($timezone);
        $start = $local->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = $local->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        return [
            'key' => $start->isoFormat('GGGG-[W]WW'),
            'start' => $start->copy()->utc(),
            'end' => $end->copy()->utc(),
        ];
    }

    /**
     * @return Collection<int, array{key: string, start: CarbonInterface, end: CarbonInterface}>
     */
    public function periodsBetween(CarbonInterface $from, CarbonInterface $to, string $timezone): Collection
    {
        $periods = collect();
        $cursor = Carbon::parse($from)->timezone($timezone)->startOfDay();
        $endBoundary = Carbon::parse($to)->timezone($timezone)->endOfDay();

        while ($cursor->lte($endBoundary)) {
            $period = $this->periodContaining($cursor, $timezone);

            if (! $periods->contains(fn (array $existing): bool => $existing['key'] === $period['key'])) {
                $periods->push($period);
            }

            $cursor = Carbon::parse($period['end'])->timezone($timezone)->addDay()->startOfDay();
        }

        return $periods;
    }
}
