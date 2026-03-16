<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialSubscriptions;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscription;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncFeedSubscriptionsForRule
{
    use AsAction;

    public function handle(FeedSubscriptionRule $rule): void
    {
        $rule->loadMissing('feed.owner');
        $feed = $rule->feed;
        if ($feed === null || $feed->owner === null) {
            return;
        }

        $feedOwner = $feed->owner;
        $subscribers = $this->resolveSubscribersForScope($rule);
        $teamId = $this->resolveTeamIdForRule($rule);

        if (! $rule->auto_subscribe) {
            $this->removeSubscriptionsForRule($rule);

            return;
        }

        $subscriberKeys = [];
        foreach ($subscribers as $subscriber) {
            if (! $subscriber instanceof Model) {
                continue;
            }
            if (! in_array(HasSocialSubscriptions::class, class_uses_recursive($subscriber), true)) {
                continue;
            }
            $this->ensureSubscriptionExists($subscriber, $feedOwner, $rule, $teamId);
            $subscriberKeys[] = $subscriber->getMorphClass().'|'.$subscriber->getKey();
        }

        $this->removeSubscriptionsNotInSet($rule, $subscriberKeys);
    }

    /**
     * @return iterable<int, Model>
     */
    protected function resolveSubscribersForScope(FeedSubscriptionRule $rule): iterable
    {
        $resolvers = config('filament-social-graph.subscription_rule_scope_resolver', []);
        $scope = $rule->scope;
        if (! is_array($resolvers) || ! isset($resolvers[$scope]) || ! is_callable($resolvers[$scope])) {
            return [];
        }

        $result = $resolvers[$scope]($rule);

        return is_array($result) ? $result : (is_iterable($result) ? $result : []);
    }

    protected function resolveTeamIdForRule(FeedSubscriptionRule $rule): ?string
    {
        if (! config('filament-social-graph.tenancy.enabled')) {
            return null;
        }
        if (! Schema::hasColumn($rule->getTable(), 'team_id')) {
            return null;
        }

        return $rule->team_id;
    }

    protected function ensureSubscriptionExists(
        Model $subscriber,
        Model $feedOwner,
        FeedSubscriptionRule $rule,
        ?string $teamId
    ): void {
        $match = [
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
            'feed_owner_type' => $feedOwner->getMorphClass(),
            'feed_owner_id' => $feedOwner->getKey(),
        ];
        $additional = ['subscription_rule_id' => $rule->getKey()];
        if ($teamId !== null && Schema::hasColumn((new FeedSubscription)->getTable(), 'team_id')) {
            $additional['team_id'] = $teamId;
        }
        FeedSubscription::updateOrCreate($match, $additional);
    }

    protected function removeSubscriptionsForRule(FeedSubscriptionRule $rule): void
    {
        FeedSubscription::query()
            ->where('subscription_rule_id', $rule->getKey())
            ->delete();
    }

    /**
     * Remove FeedSubscription rows for this rule whose subscriber is not in the given set.
     *
     * @param  array<int, string>  $subscriberKeys  Morph class and id joined by |
     */
    protected function removeSubscriptionsNotInSet(FeedSubscriptionRule $rule, array $subscriberKeys): void
    {
        $subs = FeedSubscription::query()
            ->where('subscription_rule_id', $rule->getKey())
            ->get(['id', 'subscriber_type', 'subscriber_id']);

        $keySet = array_flip($subscriberKeys);
        foreach ($subs as $sub) {
            $key = $sub->subscriber_type.'|'.$sub->subscriber_id;
            if (! isset($keySet[$key])) {
                $sub->delete();
            }
        }
    }
}
