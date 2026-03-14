<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Jobs\GenerateFeedItemThumbnailsJob;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class FeedItemThumbnailJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_saving_feed_item_with_image_attachments_dispatches_job(): void
    {
        Queue::fake();

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
            'attachments' => [],
        ]);
        $feedItem->update(['attachments' => [$path]]);

        Queue::assertPushed(GenerateFeedItemThumbnailsJob::class, function (GenerateFeedItemThumbnailsJob $job) use ($feedItem): bool {
            return $job->feedItemId === $feedItem->getKey();
        });
    }

    public function test_saving_feed_item_without_image_attachments_does_not_dispatch_job(): void
    {
        Queue::fake();

        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'No attachments',
            'attachments' => [],
        ]);

        Queue::assertNotPushed(GenerateFeedItemThumbnailsJob::class);
    }

    public function test_updating_attachments_to_include_image_dispatches_job(): void
    {
        Queue::fake();

        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
        ]);
        $path = 'feed-item-attachments/later.jpg';
        Storage::disk('public')->put($path, $this->minimalJpeg());

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Initially empty',
            'attachments' => [],
        ]);

        Queue::fake();
        $feedItem->update(['attachments' => [$path]]);

        Queue::assertPushed(GenerateFeedItemThumbnailsJob::class, function (GenerateFeedItemThumbnailsJob $job) use ($feedItem): bool {
            return $job->feedItemId === $feedItem->getKey();
        });
    }

    public function test_job_generates_thumbnails(): void
    {
        $user = TestUser::create([
            'name' => 'Test',
            'email' => 'test4@example.com',
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

        $job = new GenerateFeedItemThumbnailsJob($feedItem->getKey());
        $job->handle(app(\BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService::class));

        $this->assertTrue(Storage::disk('public')->exists(FeedItem::getThumbnailPath($path)));
    }
}
