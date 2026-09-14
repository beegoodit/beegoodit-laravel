<?php

namespace BeegoodIT\FilamentStreaks\Filament\Concerns;

use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait ValidatesQualificationActivityWindow
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertWithinActivityWindow(array $data): void
    {
        $streakId = $data['streak_id'] ?? null;
        $happenedAt = $data['happened_at'] ?? null;

        if ($streakId === null || $happenedAt === null) {
            return;
        }

        $streak = Streak::query()->with(['scope'])->find($streakId);

        if ($streak === null) {
            return;
        }

        $strategy = app(StrategyResolver::class)->resolve($streak->strategy_identifier);
        $activeFrom = $strategy->activeFrom($streak->scope);
        $activeTo = $strategy->activeTo($streak->scope);
        $happened = Carbon::parse($happenedAt);

        if ($activeFrom !== null && $happened->lt(Carbon::parse($activeFrom)->startOfDay())) {
            throw ValidationException::withMessages([
                'happened_at' => __('filament-streaks::qualification.validation.outside_activity_window'),
            ]);
        }

        if ($activeTo !== null && $happened->gt(Carbon::parse($activeTo)->endOfDay())) {
            throw ValidationException::withMessages([
                'happened_at' => __('filament-streaks::qualification.validation.outside_activity_window'),
            ]);
        }
    }
}
