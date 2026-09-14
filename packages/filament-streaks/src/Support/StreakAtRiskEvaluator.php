<?php

namespace BeegoodIT\FilamentStreaks\Support;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Support\Carbon;

class StreakAtRiskEvaluator
{
    public function __construct(
        private StrategyResolver $strategyResolver,
        private DurationParser $durationParser,
    ) {}

    /**
     * Simple at-risk gate: within lead before period end, open streak, no qual in current period.
     * Does not require an upcoming host event.
     */
    public function isAtRisk(Streak $streak, ?Carbon $asOf = null): bool
    {
        $subject = $streak->subject;

        if ($subject === null || ! method_exists($subject, 'getTimezone')) {
            return false;
        }

        if (! $streak->isOpen()) {
            return false;
        }

        $strategyConfig = $this->strategyResolver->config($streak->strategy_identifier);
        $strategy = $this->strategyResolver->resolve($streak->strategy_identifier);
        $timezone = $subject->getTimezone();
        $now = ($asOf ?? Carbon::now())->timezone($timezone);
        $period = $strategy->periodicity()->periodContaining($now, $timezone);
        $lead = $this->durationParser->toInterval($strategyConfig['at_risk']['lead'] ?? '24h');
        $periodEnd = Carbon::parse($period['end'])->timezone($timezone);

        if ($now->lt($periodEnd->copy()->sub($lead)) || $now->gt($periodEnd)) {
            return false;
        }

        return ! $this->hasQualificationInPeriod($streak, $period);
    }

    /**
     * @param  array{key: string, start: \Carbon\CarbonInterface, end: \Carbon\CarbonInterface}  $period
     */
    public function hasQualificationInPeriod(Streak $streak, array $period): bool
    {
        if ($streak->relationLoaded('qualifications')) {
            return $streak->qualifications->contains(function ($qualification) use ($period): bool {
                $happenedAt = Carbon::parse($qualification->happened_at);

                return $happenedAt->betweenIncluded($period['start'], $period['end']);
            });
        }

        return $streak->qualifications()
            ->whereBetween('happened_at', [$period['start'], $period['end']])
            ->exists();
    }

    /**
     * @return array{key: string, start: \Carbon\CarbonInterface, end: \Carbon\CarbonInterface}|null
     */
    public function currentPeriod(Streak $streak, ?Carbon $asOf = null): ?array
    {
        $subject = $streak->subject;

        if ($subject === null || ! method_exists($subject, 'getTimezone')) {
            return null;
        }

        $strategy = $this->strategyResolver->resolve($streak->strategy_identifier);
        $timezone = $subject->getTimezone();
        $now = ($asOf ?? Carbon::now())->timezone($timezone);

        return $strategy->periodicity()->periodContaining($now, $timezone);
    }
}
