<?php

namespace BeegoodIT\FilamentSocialGraph\Observers;

use BeegoodIT\FilamentSocialGraph\Actions\SyncFeedSubscriptionsForRule;
use BeegoodIT\FilamentSocialGraph\Models\Feed;

class FeedObserver
{
    public function saved(Feed $feed): void
    {
        $feed->loadMissing('feedSubscriptionRules');
        foreach ($feed->feedSubscriptionRules as $rule) {
            SyncFeedSubscriptionsForRule::run($rule);
        }
    }
}
