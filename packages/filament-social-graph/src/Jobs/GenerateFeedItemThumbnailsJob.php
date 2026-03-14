<?php

namespace BeegoodIT\FilamentSocialGraph\Jobs;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateFeedItemThumbnailsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $feedItemId
    ) {}

    public function handle(FeedItemThumbnailService $thumbnailService): void
    {
        $feedItem = FeedItem::find($this->feedItemId);
        if ($feedItem === null) {
            return;
        }

        $attachments = $feedItem->attachments ?? [];
        if ($attachments === []) {
            return;
        }

        $disk = FeedItem::getStorageDisk();
        foreach ($attachments as $path) {
            if (FeedItem::isImagePath($path)) {
                $thumbnailService->generateThumbnail($disk, $path);
            }
        }
    }
}
