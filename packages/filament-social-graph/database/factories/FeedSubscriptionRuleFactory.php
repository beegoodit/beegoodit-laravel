<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule>
 */
class FeedSubscriptionRuleFactory extends Factory
{
    protected $model = FeedSubscriptionRule::class;

    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => true,
        ];
    }

    public function forFeed(Feed $feed): static
    {
        return $this->state(fn (): array => [
            'feed_id' => $feed->getKey(),
        ]);
    }
}
