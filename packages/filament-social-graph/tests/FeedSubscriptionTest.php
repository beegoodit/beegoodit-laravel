<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;

class FeedSubscriptionTest extends TestCase
{
    public function test_it_can_subscribe_to_a_feed(): void
    {
        $subscriber = TestUser::create([
            'name' => 'Subscriber',
            'email' => 'sub@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedOwner = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $subscription = $subscriber->subscribeTo($feedOwner);

        $this->assertDatabaseHas('feed_subscriptions', [
            'id' => $subscription->id,
            'subscriber_type' => TestUser::class,
            'subscriber_id' => $subscriber->id,
            'feed_owner_type' => TestUser::class,
            'feed_owner_id' => $feedOwner->id,
        ]);

        $this->assertTrue($subscriber->isSubscribedTo($feedOwner));
    }

    public function test_it_can_unsubscribe_from_a_feed(): void
    {
        $subscriber = TestUser::create([
            'name' => 'Subscriber',
            'email' => 'sub2@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedOwner = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password'),
        ]);

        $subscriber->subscribeTo($feedOwner);
        $this->assertTrue($subscriber->isSubscribedTo($feedOwner));

        $subscriber->unsubscribeFrom($feedOwner);
        $this->assertFalse($subscriber->isSubscribedTo($feedOwner));
    }

    public function test_subscribe_is_idempotent(): void
    {
        $subscriber = TestUser::create([
            'name' => 'Subscriber',
            'email' => 'sub3@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedOwner = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner3@example.com',
            'password' => bcrypt('password'),
        ]);

        $sub1 = $subscriber->subscribeTo($feedOwner);
        $sub2 = $subscriber->subscribeTo($feedOwner);

        $this->assertEquals($sub1->id, $sub2->id);
        $this->assertEquals(1, FeedSubscription::count());
    }
}
