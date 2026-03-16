<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedSubscriptionRuleRequest;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Support\Facades\Config;
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

    public function test_team_id_is_set_from_feed_owner_when_tenancy_enabled_and_owner_is_team(): void
    {
        Config::set('filament-social-graph.tenancy.enabled', true);
        Config::set('filament-social-graph.tenancy.team_model', TestTeam::class);

        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create([
            'scope' => 'team_members',
            'auto_subscribe' => true,
        ]);

        $this->assertSame($team->getKey(), $rule->team_id);
        $this->assertDatabaseHas('feed_subscription_rules', [
            'id' => $rule->getKey(),
            'team_id' => $team->getKey(),
        ]);
    }

    public function test_team_id_is_null_when_feed_owner_is_not_team_or_tenancy_disabled(): void
    {
        Config::set('filament-social-graph.tenancy.enabled', false);

        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create();

        $this->assertNull($rule->team_id);
    }

    public function test_team_id_updates_when_rule_is_updated_to_different_feed_with_team_owner(): void
    {
        Config::set('filament-social-graph.tenancy.enabled', true);
        Config::set('filament-social-graph.tenancy.team_model', TestTeam::class);

        $team1 = TestTeam::create(['name' => 'T1']);
        $team2 = TestTeam::create(['name' => 'T2']);
        $feed1 = Feed::factory()->forOwner($team1)->create();
        $feed2 = Feed::factory()->forOwner($team2)->create();

        $rule = FeedSubscriptionRule::factory()->forFeed($feed1)->create();
        $this->assertSame($team1->getKey(), $rule->team_id);

        $rule->update(['feed_id' => $feed2->getKey()]);
        $rule->refresh();
        $this->assertSame($team2->getKey(), $rule->team_id);
    }
}
