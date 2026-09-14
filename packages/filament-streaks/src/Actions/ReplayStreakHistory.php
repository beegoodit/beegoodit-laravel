<?php

namespace BeegoodIT\FilamentStreaks\Actions;

use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ReplayStreakHistory
{
    use AsAction;

    public function __construct(
        private RecordStreakEvents $recordStreakEvents,
        private EvaluateStreakPeriods $evaluateStreakPeriods,
        private StrategyResolver $strategyResolver,
    ) {}

    /**
     * @return array{from: string, to: string, evaluated_as_of: string, strategies: int, record_calls: int, streaks_broken: int}
     */
    public function handle(CarbonInterface $from, CarbonInterface $to): array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $strategies = array_keys(config('filament-streaks.strategies', []));
        $recordCalls = 0;

        foreach ($strategies as $strategyIdentifier) {
            $strategy = $this->strategyResolver->resolve($strategyIdentifier);

            $strategy->subjects()->each(function ($subject) use (
                $strategyIdentifier,
                $strategy,
                $from,
                $to,
                &$recordCalls,
            ): void {
                if ($strategy->scopeType() === null) {
                    $this->recordStreakEvents->handle($strategyIdentifier, $subject, null, $from, $to);
                    $recordCalls++;

                    return;
                }

                $strategy->scopeType()::query()->each(function ($scope) use (
                    $strategyIdentifier,
                    $subject,
                    $from,
                    $to,
                    &$recordCalls,
                ): void {
                    $this->recordStreakEvents->handle($strategyIdentifier, $subject, $scope, $from, $to);
                    $recordCalls++;
                });
            });
        }

        // Never evaluate as-of a future date: a year-end `to` would close live runs.
        $evaluateAsOf = Carbon::now()->min($to);
        $streaksBroken = $this->evaluateStreakPeriods->handle($evaluateAsOf);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'evaluated_as_of' => $evaluateAsOf->toDateString(),
            'strategies' => count($strategies),
            'record_calls' => $recordCalls,
            'streaks_broken' => $streaksBroken,
        ];
    }
}
