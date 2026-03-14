<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteFeedItem
{
    use AsAction;

    public function handle(FeedItem $feedItem): void
    {
        $feedItem->delete();
    }
}
