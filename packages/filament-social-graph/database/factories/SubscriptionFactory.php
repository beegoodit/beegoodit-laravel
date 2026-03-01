<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'subscriber_type' => \App\Models\User::class,
            'subscriber_id' => \Illuminate\Support\Str::uuid(),
            'feed_owner_type' => \App\Models\User::class,
            'feed_owner_id' => \Illuminate\Support\Str::uuid(),
        ];
    }
}
