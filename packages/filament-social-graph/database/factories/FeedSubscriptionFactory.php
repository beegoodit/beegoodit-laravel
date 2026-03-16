<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BeegoodIT\FilamentSocialGraph\Models\FeedSubscription>
 */
class FeedSubscriptionFactory extends Factory
{
    protected $model = FeedSubscription::class;

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
