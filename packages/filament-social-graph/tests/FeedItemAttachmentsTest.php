<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;

class FeedItemAttachmentsTest extends TestCase
{
    public function test_feed_item_stores_attachments_as_json_array(): void
    {
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $paths = [
            'feed-item-attachments/photo.jpg',
            'feed-item-attachments/document.pdf',
        ];

        $feed = Feed::firstOrCreateForOwner($user);
        $feedItem = FeedItem::create([
            'feed_id' => $feed->getKey(),
            'body' => 'With attachments',
            'attachments' => $paths,
        ]);

        $this->assertSame($paths, $feedItem->attachments);
        $this->assertIsArray($feedItem->attachments);
        $this->assertCount(2, $feedItem->attachments);
    }
}
