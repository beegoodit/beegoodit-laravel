<?php

namespace BeegoodIT\FilamentStreaks\Database\Factories;

use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Qualification>
 */
class QualificationFactory extends Factory
{
    protected $model = Qualification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'streak_id' => Streak::factory(),
            'event_type' => 'streak-events',
            'event_id' => (string) Str::uuid(),
            'happened_at' => now(),
        ];
    }
}
