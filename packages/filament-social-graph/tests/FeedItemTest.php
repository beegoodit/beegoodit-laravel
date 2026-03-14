<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Auth;

class FeedItemTest extends TestCase
{
    public function test_it_can_create_a_feed_item(): void
    {
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $feedItem = $user->createFeedItem([
            'subject' => 'Hello World',
            'body' => 'This is a test post.',
        ]);

        $this->assertDatabaseHas('feed_items', [
            'id' => $feedItem->id,
            'feed_id' => $feedItem->feed_id,
            'subject' => 'Hello World',
            'body' => 'This is a test post.',
        ]);
        $this->assertSame(TestUser::class, $feedItem->feed->owner_type);
        $this->assertSame((string) $user->id, (string) $feedItem->feed->owner_id);
    }

    public function test_feed_item_has_owner_relationship(): void
    {
        $user = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feed = Feed::firstOrCreateForOwner($user);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'Test',
        ]);

        $this->assertEquals($user->id, $feedItem->owner->id);
        $this->assertEquals('Actor', $feedItem->owner->name);
    }

    public function test_feed_item_tracks_userstamps(): void
    {
        $user = TestUser::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $feedItem = $user->createFeedItem([
            'body' => 'Test',
        ]);

        $this->assertEquals($user->id, $feedItem->created_by_id);
    }
}
