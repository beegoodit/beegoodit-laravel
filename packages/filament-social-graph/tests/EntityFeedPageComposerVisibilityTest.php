<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\EntityFeedPage;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

class EntityFeedPageComposerVisibilityTest extends TestCase
{
    public function test_composer_shown_when_policy_allows(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        config()->set('filament-social-graph.actor_models', [TestUser::class]);

        $entity = TestUser::create([
            'name' => 'Entity',
            'email' => 'entity@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(EntityFeedPage::class, ['entity' => $entity])
            ->assertSee(__('filament-social-graph::feed_item.subject'));
    }

    public function test_composer_hidden_when_policy_denies(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $entity = TestUser::create([
            'name' => 'Entity',
            'email' => 'entity@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(EntityFeedPage::class, ['entity' => $entity])
            ->assertDontSee(__('filament-social-graph::feed.composer_placeholder'));
    }
}
