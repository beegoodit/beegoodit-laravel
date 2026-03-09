<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteFeedItem
{
    use AsAction;

    public function handle(FeedItem $feedItem): void
    {
        $this->deleteStoredAttachments($feedItem);
        $feedItem->delete();
    }

    protected function deleteStoredAttachments(FeedItem $feedItem): void
    {
        $paths = $feedItem->attachments ?? [];
        if ($paths === []) {
            return;
        }
        $disk = FeedItem::getStorageDisk();
        foreach ($paths as $path) {
            Storage::disk($disk)->delete($path);
            if (FeedItem::isImagePath($path)) {
                Storage::disk($disk)->delete(FeedItem::getThumbnailPath($path));
            }
        }
    }
}
