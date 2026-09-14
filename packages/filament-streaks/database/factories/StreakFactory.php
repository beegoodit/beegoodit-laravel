<?php

namespace BeegoodIT\FilamentStreaks\Database\Factories;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Streak>
 */
class StreakFactory extends Factory
{
    protected $model = Streak::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userModel = config('filament-streaks.user_model');
        $user = $userModel::query()->first() ?? $userModel::query()->create([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
        ]);

        return [
            'strategy_identifier' => 'test_strategy',
            'subject_type' => $userModel,
            'subject_id' => $user->getKey(),
            'scope_type' => null,
            'scope_id' => null,
            'start_at' => now()->subWeek()->toDateString(),
            'end_at' => null,
        ];
    }

    public function ended(): static
    {
        return $this->state(fn (): array => [
            'end_at' => now()->toDateString(),
        ]);
    }
}
