<?php

namespace BeegoodIT\FilamentStreaks\Actions;

use BeegoodIT\FilamentStreaks\Data\StreakEventData;
use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use BeegoodIT\FilamentStreaks\Support\StreakPeriodBounds;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class RecordStreakEvents
{
    use AsAction;

    public function __construct(
        private StrategyResolver $strategyResolver,
        private StreakPeriodBounds $periodBounds,
    ) {}

    public function handle(
        string $strategyIdentifier,
        Model $subject,
        ?Model $scope = null,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
    ): Streak {
        $strategyConfig = $this->strategyResolver->config($strategyIdentifier);
        $strategy = $this->strategyResolver->resolve($strategyIdentifier);

        if (! is_a($subject, $strategy->subjectType())) {
            throw new \InvalidArgumentException('Subject type does not match strategy.');
        }

        if (! $strategy->subjects()->whereKey($subject->getKey())->exists()) {
            throw new \InvalidArgumentException('Subject is not eligible for this strategy.');
        }

        if ($scope !== null && $strategy->scopeType() !== $scope->getMorphClass()) {
            throw new \InvalidArgumentException('Scope type does not match strategy.');
        }

        if ($strategy->scopeType() === null && $scope !== null) {
            throw new \InvalidArgumentException('Strategy does not accept a scope.');
        }

        if ($strategy->scopeType() !== null && $scope === null) {
            throw new \InvalidArgumentException('Strategy requires a scope.');
        }

        $timezone = $this->resolveTimezone($subject);
        $now = Carbon::now($timezone);
        $activeFrom = $strategy->activeFrom($scope);
        $activeTo = $strategy->activeTo($scope);

        if ($activeTo !== null && $now->gt(Carbon::parse($activeTo, $timezone)->endOfDay())) {
            return $this->findOrNewStreak($strategyIdentifier, $subject, $scope);
        }

        $rangeFrom = $from ?? $activeFrom ?? Carbon::parse('1970-01-01', $timezone);
        $rangeTo = $to ?? $now;

        if ($activeFrom !== null) {
            $rangeFrom = Carbon::parse($rangeFrom)->max(Carbon::parse($activeFrom, $timezone)->startOfDay());
        }

        if ($activeTo !== null) {
            $rangeTo = Carbon::parse($rangeTo)->min(Carbon::parse($activeTo, $timezone)->endOfDay());
        }

        $events = $strategy->events($subject, $scope, $rangeFrom, $rangeTo)
            ->sortBy(fn (StreakEventData $event): int => $event->happenedAt->getTimestamp())
            ->values();

        if ($events->isEmpty()) {
            return $this->findOrNewStreak($strategyIdentifier, $subject, $scope);
        }

        $periodicity = $strategy->periodicity();
        $openOnFirstEvent = ($strategyConfig['open_on'] ?? 'first_event') === 'first_event';
        $streak = null;

        foreach ($events as $event) {
            if ($activeFrom !== null && $event->happenedAt->lt(Carbon::parse($activeFrom, $timezone)->startOfDay())) {
                continue;
            }

            if ($activeTo !== null && $event->happenedAt->gt(Carbon::parse($activeTo, $timezone)->endOfDay())) {
                continue;
            }

            $streak = $this->findOpenStreak($strategyIdentifier, $subject, $scope);

            if ($streak !== null) {
                $streak->loadMissing('qualifications');

                if ($this->periodBounds->hasGapBeforeEvent(
                    $periodicity,
                    $timezone,
                    $streak->start_at->toDateString(),
                    $streak->qualifications,
                    Carbon::parse($event->happenedAt),
                )) {
                    $streak->update([
                        'end_at' => $this->periodBounds->lastQualifiedPeriodEndDate(
                            $streak,
                            $periodicity,
                            $timezone,
                        ),
                    ]);
                    $streak = null;
                }
            }

            if ($streak === null && $openOnFirstEvent) {
                $period = $periodicity->periodContaining($event->happenedAt, $timezone);

                $streak = Streak::query()->create([
                    'strategy_identifier' => $strategyIdentifier,
                    'subject_type' => $subject->getMorphClass(),
                    'subject_id' => $subject->getKey(),
                    'scope_type' => $scope?->getMorphClass(),
                    'scope_id' => $scope?->getKey(),
                    'start_at' => Carbon::parse($period['start'])->timezone($timezone)->toDateString(),
                ]);
            }

            if ($streak === null) {
                continue;
            }

            Qualification::query()->updateOrCreate(
                [
                    'streak_id' => $streak->getKey(),
                    'event_type' => $event->eventType,
                    'event_id' => $event->eventId,
                ],
                [
                    'happened_at' => $event->happenedAt,
                ],
            );

            $streak->unsetRelation('qualifications');
        }

        return $streak?->fresh(['qualifications'])
            ?? $this->findOrNewStreak($strategyIdentifier, $subject, $scope);
    }

    private function resolveTimezone(Model $subject): string
    {
        if (! method_exists($subject, 'getTimezone')) {
            throw new \InvalidArgumentException('Subject must implement getTimezone().');
        }

        return $subject->getTimezone();
    }

    private function findOpenStreak(string $strategyIdentifier, Model $subject, ?Model $scope): ?Streak
    {
        return Streak::query()
            ->open()
            ->where('strategy_identifier', $strategyIdentifier)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->when(
                $scope === null,
                fn ($query) => $query->whereNull('scope_type')->whereNull('scope_id'),
                fn ($query) => $query->where('scope_type', $scope->getMorphClass())->where('scope_id', $scope->getKey()),
            )
            ->first();
    }

    private function findOrNewStreak(string $strategyIdentifier, Model $subject, ?Model $scope): Streak
    {
        return $this->findOpenStreak($strategyIdentifier, $subject, $scope)
            ?? new Streak([
                'strategy_identifier' => $strategyIdentifier,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'scope_type' => $scope?->getMorphClass(),
                'scope_id' => $scope?->getKey(),
            ]);
    }
}
