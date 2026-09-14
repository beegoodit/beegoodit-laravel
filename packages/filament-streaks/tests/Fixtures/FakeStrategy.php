<?php

namespace BeegoodIT\FilamentStreaks\Tests\Fixtures;

use BeegoodIT\FilamentStreaks\Contracts\PeriodicityContract;
use BeegoodIT\FilamentStreaks\Contracts\StrategyContract;
use BeegoodIT\FilamentStreaks\Data\StreakEventData;
use BeegoodIT\FilamentStreaks\Strategies\IsoWeekStrategy;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FakeStrategy implements StrategyContract
{
    public static ?CarbonInterface $activeFrom = null;

    public static ?CarbonInterface $activeTo = null;

    public static function reset(): void
    {
        self::$activeFrom = null;
        self::$activeTo = null;
    }

    public function subjectType(): string
    {
        return User::class;
    }

    public function subjects(): Builder
    {
        return User::query();
    }

    public function scopeType(): ?string
    {
        return null;
    }

    public function periodicity(): PeriodicityContract
    {
        return new IsoWeekStrategy;
    }

    public function activeFrom(?Model $scope): ?CarbonInterface
    {
        return self::$activeFrom;
    }

    public function activeTo(?Model $scope): ?CarbonInterface
    {
        return self::$activeTo;
    }

    public function events(Model $subject, ?Model $scope, CarbonInterface $periodStart, CarbonInterface $periodEnd): Collection
    {
        return StreakEvent::query()
            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
            ->get()
            ->map(fn (StreakEvent $event): StreakEventData => new StreakEventData(
                happenedAt: Carbon::parse($event->occurred_at)->utc(),
                eventType: $event->getMorphClass(),
                eventId: (string) $event->getKey(),
            ));
    }
}
