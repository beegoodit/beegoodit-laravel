<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedSubscriptionRuleRequest;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Support\Facades\Validator;

class FeedSubscriptionRuleTest extends TestCase
{
    public function test_it_can_create_a_rule(): void
    {
        $feed = Feed::factory()->forOwner(TestTeam::create(['name' => 'T1']))->create();

        $rule = FeedSubscriptionRule::query()->create([
            'feed_id' => $feed->getKey(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => true,
        ]);

        $this->assertDatabaseHas('feed_subscription_rules', [
            'id' => $rule->id,
            'feed_id' => $feed->getKey(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => true,
        ]);

        $this->assertTrue($rule->auto_subscribe);
        $this->assertTrue($rule->unsubscribable);
        $this->assertSame($feed->getKey(), $rule->feed_id);
    }

    public function test_scope_must_be_in_config_keys_when_validating(): void
    {
        $feed = Feed::factory()->forOwner(TestTeam::create(['name' => 'T1']))->create();
        $request = new StoreFeedSubscriptionRuleRequest;
        $request->setContainer($this->app);

        $data = [
            'feed_id' => $feed->getKey(),
            'scope' => 'invalid_scope',
            'auto_subscribe' => true,
            'unsubscribable' => false,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    public function test_multiple_rules_per_feed_allowed(): void
    {
        $feed = Feed::factory()->forOwner(TestTeam::create(['name' => 'T1']))->create();

        FeedSubscriptionRule::query()->create([
            'feed_id' => $feed->getKey(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => false,
        ]);

        $rule2 = FeedSubscriptionRule::query()->create([
            'feed_id' => $feed->getKey(),
            'scope' => 'team_members',
            'auto_subscribe' => false,
            'unsubscribable' => true,
        ]);

        $this->assertDatabaseCount('feed_subscription_rules', 2);
        $this->assertSame($feed->getKey(), $rule2->feed_id);
    }

    public function test_feed_relationship(): void
    {
        $feed = Feed::factory()->forOwner(TestTeam::create(['name' => 'T1']))->create();
        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create();

        $this->assertTrue($rule->feed->is($feed));
    }
}
