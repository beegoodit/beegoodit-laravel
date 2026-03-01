<?php

namespace BeegoodIT\FilamentSocialGraph\Models\Observers;

use BeegoodIT\FilamentSocialGraph\Models\FeedItemAttachment;
use Illuminate\Support\Facades\Storage;

class FeedItemAttachmentObserver
{
    public function deleted(FeedItemAttachment $attachment): void
    {
        if (! empty($attachment->path) && Storage::exists($attachment->path)) {
            Storage::delete($attachment->path);
        }
    }
}
