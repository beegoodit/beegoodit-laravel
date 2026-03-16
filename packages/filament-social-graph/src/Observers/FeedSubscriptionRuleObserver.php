<?php

namespace BeegoodIT\FilamentSocialGraph\Observers;

use BeegoodIT\FilamentSocialGraph\Actions\SyncFeedSubscriptionsForRule;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;

class FeedSubscriptionRuleObserver
{
    public function saved(FeedSubscriptionRule $rule): void
    {
        SyncFeedSubscriptionsForRule::run($rule);
    }
}
