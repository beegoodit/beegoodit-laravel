<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule>
 */
class FeedSubscriptionRuleFactory extends Factory
{
    protected $model = FeedSubscriptionRule::class;

    public function definition(): array
    {
        return [
            'subscribable_type' => Feed::class,
            'subscribable_id' => Str::uuid(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => true,
        ];
    }

    public function forSubscribable(\Illuminate\Database\Eloquent\Model $subscribable): static
    {
        return $this->state(fn (): array => [
            'subscribable_type' => $subscribable->getMorphClass(),
            'subscribable_id' => $subscribable->getKey(),
        ]);
    }
}
