<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\AttachmentType;
use BeegoodIT\FilamentSocialGraph\Models\FeedItemAttachment;

class FeedItemAttachmentTest extends TestCase
{
    public function test_attachment_belongs_to_feed_item(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = $user->createFeedItem([
            'body' => 'With attachment',
            'visibility' => \BeegoodIT\FilamentSocialGraph\Enums\Visibility::Public,
        ]);

        $attachment = FeedItemAttachment::create([
            'feed_item_id' => $feedItem->id,
            'type' => AttachmentType::File,
            'path' => 'attachments/test.pdf',
            'filename' => 'test.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertEquals($feedItem->id, $attachment->feedItem->id);
    }

    public function test_feed_item_has_attachments(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = $user->createFeedItem([
            'body' => 'With attachments',
            'visibility' => \BeegoodIT\FilamentSocialGraph\Enums\Visibility::Public,
        ]);

        FeedItemAttachment::create([
            'feed_item_id' => $feedItem->id,
            'type' => AttachmentType::Image,
            'path' => 'attachments/photo.jpg',
            'filename' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $this->assertCount(1, $feedItem->fresh()->attachments);
        $this->assertTrue($feedItem->attachments->first()->isImage());
    }
}
