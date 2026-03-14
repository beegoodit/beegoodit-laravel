<?php

namespace BeegoodIT\FilamentSocialGraph\Observers;

use BeegoodIT\FilamentSocialGraph\Jobs\GenerateFeedItemThumbnailsJob;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;

class FeedItemObserver
{
    public function saved(FeedItem $feedItem): void
    {
        if (! $feedItem->wasChanged('attachments')) {
            return;
        }

        $attachments = $feedItem->attachments ?? [];
        $hasImage = false;
        foreach ($attachments as $path) {
            if (FeedItem::isImagePath($path)) {
                $hasImage = true;
                break;
            }
        }

        if ($hasImage) {
            GenerateFeedItemThumbnailsJob::dispatch($feedItem->getKey());
        }
    }
}
