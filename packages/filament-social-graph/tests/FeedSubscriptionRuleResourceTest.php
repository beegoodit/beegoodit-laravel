<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages\CreateFeedSubscriptionRule;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages\EditFeedSubscriptionRule;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages\ListFeedSubscriptionRules;
use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedSubscriptionRuleRequest;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use BeegoodIT\FilamentSocialGraph\Models\FeedSubscriptionRule;

class FeedSubscriptionRuleResourceTest extends TestCase
{
    public function test_resource_has_list_create_edit_pages_only_no_view(): void
    {
        $pages = FeedSubscriptionRuleResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        $this->assertArrayNotHasKey('view', $pages);
        $this->assertSame(ListFeedSubscriptionRules::class, $pages['index']->getPage());
        $this->assertSame(CreateFeedSubscriptionRule::class, $pages['create']->getPage());
        $this->assertSame(EditFeedSubscriptionRule::class, $pages['edit']->getPage());
    }

    public function test_scope_validation_rules_come_from_form_request(): void
    {
        $rules = StoreFeedSubscriptionRuleRequest::scopeValidationRules();

        $this->assertContains('required', $rules);
        $this->assertContains('string', $rules);
        $this->assertCount(3, $rules);
    }

    public function test_can_create_rule_via_factory_and_resource_model_matches(): void
    {
        $feed = Feed::factory()->forOwner(TestTeam::create(['name' => 'T1']))->create();
        $rule = FeedSubscriptionRule::factory()->forSubscribable($feed)->create([
            'scope' => 'all_users',
            'auto_subscribe' => true,
            'unsubscribable' => false,
        ]);

        $this->assertDatabaseHas('feed_subscription_rules', [
            'id' => $rule->id,
            'subscribable_type' => Feed::class,
            'subscribable_id' => $feed->getKey(),
            'scope' => 'all_users',
        ]);
        $this->assertSame(FeedSubscriptionRule::class, FeedSubscriptionRuleResource::getModel());
    }
}
