<?php

namespace BeegoodIT\FilamentStreaks\Database\Factories;

use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationRun>
 */
class OperationRunFactory extends Factory
{
    protected $model = OperationRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => OperationRun::TYPE_STREAK_HISTORY,
            'params' => [
                'from' => '2026-01-01',
                'to' => '2026-03-31',
            ],
            'status' => OperationRunStatus::Queued,
            'result' => null,
            'error' => null,
            'requested_by' => null,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (): array => [
            'status' => OperationRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function succeeded(): static
    {
        return $this->state(fn (): array => [
            'status' => OperationRunStatus::Succeeded,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'result' => [
                'strategies' => 1,
                'record_calls' => 0,
                'streaks_broken' => 0,
            ],
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => OperationRunStatus::Failed,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'error' => 'Something went wrong.',
        ]);
    }
}
