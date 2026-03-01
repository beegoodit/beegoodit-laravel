<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
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
            'visibility' => Visibility::Public,
        ]);

        $this->assertDatabaseHas('feed_items', [
            'id' => $feedItem->id,
            'actor_type' => TestUser::class,
            'actor_id' => $user->id,
            'subject' => 'Hello World',
            'body' => 'This is a test post.',
            'visibility' => 'public',
        ]);
    }

    public function test_feed_item_has_actor_relationship(): void
    {
        $user = TestUser::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->id,
            'body' => 'Test',
            'visibility' => Visibility::Public,
        ]);

        $this->assertEquals($user->id, $feedItem->actor->id);
        $this->assertEquals('Actor', $feedItem->actor->name);
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
            'visibility' => Visibility::Public,
        ]);

        $this->assertEquals($user->id, $feedItem->created_by_id);
    }
}
