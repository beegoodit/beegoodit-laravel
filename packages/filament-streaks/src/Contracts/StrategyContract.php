<?php

namespace BeegoodIT\FilamentStreaks\Contracts;

use BeegoodIT\FilamentStreaks\Data\StreakEventData;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface StrategyContract
{
    /**
     * @return class-string<Model>
     */
    public function subjectType(): string;

    /**
     * Eligible subjects for this strategy (history walks, Admin subject picker, record guards).
     *
     * @return Builder<Model>
     */
    public function subjects(): Builder;

    public function scopeType(): ?string;

    public function periodicity(): PeriodicityContract;

    public function activeFrom(?Model $scope): ?CarbonInterface;

    public function activeTo(?Model $scope): ?CarbonInterface;

    /**
     * @return Collection<int, StreakEventData>
     */
    public function events(Model $subject, ?Model $scope, CarbonInterface $periodStart, CarbonInterface $periodEnd): Collection;
}
