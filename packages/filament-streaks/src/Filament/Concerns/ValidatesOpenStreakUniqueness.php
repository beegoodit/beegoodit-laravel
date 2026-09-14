<?php

namespace BeegoodIT\FilamentStreaks\Filament\Concerns;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

trait ValidatesOpenStreakUniqueness
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertNoOpenDuplicate(array $data, ?Streak $ignore = null): void
    {
        if (filled($data['end_at'] ?? null)) {
            return;
        }

        $exists = Streak::query()
            ->open()
            ->where('strategy_identifier', $data['strategy_identifier'] ?? null)
            ->where('subject_type', $data['subject_type'] ?? null)
            ->where('subject_id', $data['subject_id'] ?? null)
            ->when(
                ($data['scope_type'] ?? null) === null,
                fn (Builder $query) => $query->whereNull('scope_type')->whereNull('scope_id'),
                fn (Builder $query) => $query
                    ->where('scope_type', $data['scope_type'])
                    ->where('scope_id', $data['scope_id']),
            )
            ->when(
                $ignore !== null,
                fn (Builder $query) => $query->whereKeyNot($ignore->getKey()),
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'strategy_identifier' => __('filament-streaks::streak.validation.open_run_exists'),
            ]);
        }
    }
}
