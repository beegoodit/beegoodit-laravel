<?php

namespace BeegoodIT\FilamentStreaks\Support;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StreakCurrentCalculator
{
    public function __construct(
        private StrategyResolver $strategyResolver,
    ) {}

    /**
     * Duolingo-style consecutive length ending at "now" (subject TZ):
     * walk periods newest→oldest; skip an empty current period (does not inflate);
     * count consecutive periods with ≥1 qualification; stop at the first gap.
     */
    public function calculate(Streak $streak): int
    {
        $strategy = $this->strategyResolver->resolve($streak->strategy_identifier);
        $subject = $streak->subject;

        if ($subject === null || ! method_exists($subject, 'getTimezone')) {
            return 0;
        }

        $timezone = $subject->getTimezone();
        $periodicity = $strategy->periodicity();
        $now = Carbon::now($timezone);
        $from = Carbon::parse($streak->start_at, $timezone)->startOfDay();
        $to = $streak->end_at
            ? Carbon::parse($streak->end_at, $timezone)->endOfDay()
            : $now;

        /** @var Collection<int, array{key: string, start: \Carbon\CarbonInterface, end: \Carbon\CarbonInterface}> $periods */
        $periods = $periodicity->periodsBetween($from, $to, $timezone)->values();

        if ($periods->isEmpty()) {
            return 0;
        }

        $qualifications = $streak->qualifications()->get(['happened_at']);
        $currentKey = $periodicity->periodContaining($now, $timezone)['key'];

        $hasQualification = function (array $period) use ($qualifications): bool {
            return $qualifications->contains(function ($qualification) use ($period): bool {
                $happenedAt = Carbon::parse($qualification->happened_at);

                return $happenedAt->betweenIncluded($period['start'], $period['end']);
            });
        };

        $index = $periods->count() - 1;

        // Empty current period: do not inflate, keep walking from the previous period.
        if (
            $streak->end_at === null
            && $periods[$index]['key'] === $currentKey
            && ! $hasQualification($periods[$index])
        ) {
            $index--;
        }

        $count = 0;

        while ($index >= 0) {
            if (! $hasQualification($periods[$index])) {
                break;
            }

            $count++;
            $index--;
        }

        return $count;
    }
}
