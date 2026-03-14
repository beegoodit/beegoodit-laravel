<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class RegenerateFeedItemThumbnailsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_command_creates_missing_thumbnails(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $path = 'feed-item-attachments/photo.jpg';
        Storage::disk('public')->put($path, $this->minimalJpeg());

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'With image',
            'attachments' => [$path],
        ]);

        $this->assertFalse(Storage::disk('public')->exists(FeedItem::getThumbnailPath($path)));

        Artisan::call('feed-items:regenerate-thumbnails');

        $this->assertTrue(Storage::disk('public')->exists(FeedItem::getThumbnailPath($path)));
        $this->assertStringContainsString('Thumbnails created: 1', Artisan::output());
    }

    public function test_dry_run_does_not_create_files(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);
        $path = 'feed-item-attachments/photo.jpg';
        Storage::disk('public')->put($path, $this->minimalJpeg());

        FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'With image',
            'attachments' => [$path],
        ]);

        Artisan::call('feed-items:regenerate-thumbnails', ['--dry-run' => true]);

        $this->assertFalse(Storage::disk('public')->exists(FeedItem::getThumbnailPath($path)));
        $this->assertStringContainsString('Dry run', Artisan::output());
    }

    public function test_missing_only_skips_when_thumbnail_exists(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
        ]);
        $path = 'feed-item-attachments/photo.jpg';
        $thumbPath = FeedItem::getThumbnailPath($path);
        Storage::disk('public')->put($path, $this->minimalJpeg());
        Storage::disk('public')->put($thumbPath, 'existing-thumb');

        FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'With image',
            'attachments' => [$path],
        ]);

        Artisan::call('feed-items:regenerate-thumbnails', ['--missing-only' => true]);

        $this->assertSame('existing-thumb', Storage::disk('public')->get($thumbPath));
        $this->assertStringContainsString('skipped: 1', Artisan::output());
    }
}
