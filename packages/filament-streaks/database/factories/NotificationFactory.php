<?php

namespace BeegoodIT\FilamentStreaks\Database\Factories;

use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'streak_id' => Streak::factory(),
            'period_key' => now()->format('o-\WW'),
            'kind' => Notification::KIND_AT_RISK,
            'sent_at' => now(),
        ];
    }
}
