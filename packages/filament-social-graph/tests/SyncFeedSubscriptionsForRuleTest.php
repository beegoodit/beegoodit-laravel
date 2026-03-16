<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Actions\SyncFeedSubscriptionsForRule;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Support\Facades\Config;

class SyncFeedSubscriptionsForRuleTest extends TestCase
{
    public function test_creates_subscriptions_when_scope_resolver_returns_subscribers(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $user1 = TestUser::create(['name' => 'U1', 'email' => 'u1@example.com', 'password' => 'password']);
        $user2 = TestUser::create(['name' => 'U2', 'email' => 'u2@example.com', 'password' => 'password']);

        Config::set('filament-social-graph.subscription_rule_scope_resolver', [
            'all_users' => fn () => collect([$user1, $user2]),
        ]);

        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create([
            'scope' => 'all_users',
            'auto_subscribe' => true,
        ]);

        SyncFeedSubscriptionsForRule::run($rule);

        $this->assertDatabaseCount('feed_subscriptions', 2);
        $sub1 = FeedSubscription::query()
            ->where('subscriber_type', TestUser::class)
            ->where('subscriber_id', $user1->getKey())
            ->where('feed_owner_type', TestTeam::class)
            ->where('feed_owner_id', $team->getKey())
            ->first();
        $this->assertNotNull($sub1);
        $this->assertSame($rule->getKey(), $sub1->subscription_rule_id);

        $sub2 = FeedSubscription::query()
            ->where('subscriber_type', TestUser::class)
            ->where('subscriber_id', $user2->getKey())
            ->where('feed_owner_type', TestTeam::class)
            ->where('feed_owner_id', $team->getKey())
            ->first();
        $this->assertNotNull($sub2);
        $this->assertSame($rule->getKey(), $sub2->subscription_rule_id);
    }

    public function test_removes_subscriptions_when_auto_subscribe_false(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $user = TestUser::create(['name' => 'U1', 'email' => 'u1@example.com', 'password' => 'password']);

        Config::set('filament-social-graph.subscription_rule_scope_resolver', [
            'all_users' => fn () => collect([$user]),
        ]);

        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create([
            'scope' => 'all_users',
            'auto_subscribe' => true,
        ]);
        SyncFeedSubscriptionsForRule::run($rule);
        $this->assertDatabaseCount('feed_subscriptions', 1);

        $rule->update(['auto_subscribe' => false]);
        SyncFeedSubscriptionsForRule::run($rule);

        $this->assertDatabaseCount('feed_subscriptions', 0);
    }

    public function test_no_subscriptions_when_scope_resolver_not_configured(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        Config::set('filament-social-graph.subscription_rule_scope_resolver', []);

        $rule = FeedSubscriptionRule::factory()->forFeed($feed)->create([
            'scope' => 'all_users',
            'auto_subscribe' => true,
        ]);

        SyncFeedSubscriptionsForRule::run($rule);

        $this->assertDatabaseCount('feed_subscriptions', 0);
    }

    public function test_observer_syncs_subscriptions_on_rule_save(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $user = TestUser::create(['name' => 'U1', 'email' => 'u1@example.com', 'password' => 'password']);
        Config::set('filament-social-graph.subscription_rule_scope_resolver', [
            'all_users' => fn () => collect([$user]),
        ]);

        $rule = new FeedSubscriptionRule([
            'feed_id' => $feed->getKey(),
            'scope' => 'all_users',
            'auto_subscribe' => true,
        ]);
        $rule->save();

        $this->assertDatabaseCount('feed_subscriptions', 1);
        $this->assertDatabaseHas('feed_subscriptions', [
            'subscription_rule_id' => $rule->getKey(),
            'subscriber_id' => $user->getKey(),
        ]);
    }

    public function test_observer_syncs_subscriptions_on_feed_save(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();
        $user = TestUser::create(['name' => 'U1', 'email' => 'u1@example.com', 'password' => 'password']);
        Config::set('filament-social-graph.subscription_rule_scope_resolver', [
            'all_users' => fn () => collect([$user]),
        ]);

        FeedSubscriptionRule::factory()->forFeed($feed)->create([
            'scope' => 'all_users',
            'auto_subscribe' => true,
        ]);
        $this->assertDatabaseCount('feed_subscriptions', 1);

        $feed->touch();

        $this->assertDatabaseCount('feed_subscriptions', 1);
        $this->assertNotNull(
            FeedSubscription::query()
                ->where('subscriber_id', $user->getKey())
                ->where('subscription_rule_id', $feed->feedSubscriptionRules()->first()->getKey())
                ->first()
        );
    }
}
