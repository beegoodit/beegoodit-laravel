<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedItemFactory extends Factory
{
    protected $model = FeedItem::class;

    public function definition(): array
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return [
            'actor_type' => $userModel,
            'actor_id' => \Illuminate\Support\Str::uuid(),
            'subject' => $this->faker->optional(0.5)->sentence(),
            'body' => $this->faker->optional(0.8)->paragraph(),
        ];
    }
}
