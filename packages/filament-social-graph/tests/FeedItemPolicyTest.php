<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Gate;

class FeedItemPolicyTest extends TestCase
{
    public function test_guest_cannot_create_feed_item(): void
    {
        $this->assertGuest();

        $this->assertFalse(Gate::allows('create', [FeedItem::class, null]));
    }

    public function test_authenticated_user_can_create_for_global_feed(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $this->assertTrue(Gate::allows('create', [FeedItem::class, null]));
    }

    public function test_authenticated_user_can_create_for_entity_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $entity = TestUser::create([
            'name' => 'Entity',
            'email' => 'entity@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.owner_models', [TestUser::class]);

        $this->assertTrue(Gate::allows('create', [FeedItem::class, $entity]));
    }

    public function test_authenticated_user_cannot_create_for_entity_not_in_owner_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $entity = TestUser::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.owner_models', []);

        $this->assertFalse(Gate::allows('create', [FeedItem::class, $entity]));
    }

    public function test_guest_cannot_update_feed_item(): void
    {
        $this->assertGuest();

        $actor = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feed = Feed::firstOrCreateForOwner($actor);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertFalse(Gate::allows('update', $feedItem));
    }

    public function test_authenticated_user_can_update_feed_item_when_actor_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $actor = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.owner_models', [TestUser::class]);

        $feed = Feed::firstOrCreateForOwner($actor);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertTrue(Gate::allows('update', $feedItem));
    }

    public function test_authenticated_user_cannot_update_feed_item_when_actor_not_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $actor = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.owner_models', []);

        $feed = Feed::firstOrCreateForOwner($actor);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertFalse(Gate::allows('update', $feedItem));
    }

    public function test_guest_cannot_delete_feed_item(): void
    {
        $this->assertGuest();

        $actor = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feed = Feed::firstOrCreateForOwner($actor);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertFalse(Gate::allows('delete', $feedItem));
    }

    public function test_authenticated_user_can_delete_feed_item_when_actor_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $actor = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.owner_models', [TestUser::class]);

        $feed = Feed::firstOrCreateForOwner($actor);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertTrue(Gate::allows('delete', $feedItem));
    }
}
