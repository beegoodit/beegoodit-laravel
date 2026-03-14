<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Storage;

class FeedItemThumbnailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_get_thumbnail_path_returns_conventional_path_for_image(): void
    {
        $path = 'feed-item-attachments/abc.jpg';

        $thumbPath = FeedItem::getThumbnailPath($path);

        $this->assertSame('feed-item-attachments/thumbs/abc.jpg', $thumbPath);
    }

    public function test_get_thumbnail_path_handles_nested_directory(): void
    {
        $path = 'feed-item-attachments/team-123/xyz.png';

        $thumbPath = FeedItem::getThumbnailPath($path);

        $this->assertSame('feed-item-attachments/team-123/thumbs/xyz.png', $thumbPath);
    }

    public function test_get_thumbnail_url_returns_url_for_thumbnail_path(): void
    {
        $path = 'feed-item-attachments/photo.jpg';
        $thumbPath = FeedItem::getThumbnailPath($path);
        Storage::disk('public')->put($thumbPath, 'fake-image');

        $url = FeedItem::getThumbnailUrl($path);

        $this->assertNotEmpty($url);
        $this->assertStringContainsString('thumbs', $url);
        $this->assertStringContainsString('photo.jpg', $url);
    }

    public function test_get_first_image_thumbnail_url_returns_null_when_no_attachments(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'No attachments',
            'attachments' => [],
        ]);

        $this->assertNull($feedItem->getFirstImageThumbnailUrl());
    }

    public function test_get_first_image_thumbnail_url_returns_thumbnail_url_when_first_attachment_is_image(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $path = 'feed-item-attachments/img.jpg';
        $thumbPath = FeedItem::getThumbnailPath($path);
        Storage::disk('public')->put($thumbPath, 'fake');

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'With image',
            'attachments' => [$path, 'feed-item-attachments/doc.pdf'],
        ]);

        $url = $feedItem->getFirstImageThumbnailUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('thumbs', $url);
    }

    public function test_get_first_image_thumbnail_url_returns_null_when_only_non_image_attachments(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Only PDF',
            'attachments' => ['feed-item-attachments/file.pdf'],
        ]);

        $this->assertNull($feedItem->getFirstImageThumbnailUrl());
    }
}
