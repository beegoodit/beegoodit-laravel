<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Livewire\FeedComposer;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

class FeedComposerAuthorizationTest extends TestCase
{
    public function test_submit_creates_feed_item_as_entity_when_entity_set_and_policy_allows(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        config()->set('filament-social-graph.actor_models', [TestUser::class]);

        $entity = TestUser::create([
            'name' => 'Team User',
            'email' => 'team@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertCount(0, FeedItem::all());

        Livewire::test(FeedComposer::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
            ->set('subject', 'Hello')
            ->set('body', 'World')
            ->set('visibility', 'public')
            ->call('submit');

        $this->assertCount(1, FeedItem::all());

        $item = FeedItem::first();
        $this->assertSame($entity->getMorphClass(), $item->actor_type);
        $this->assertSame($entity->getKey(), $item->actor_id);
        $this->assertSame('Hello', $item->subject);
        $this->assertSame('World', $item->body);
        $this->assertTrue($item->visibility === Visibility::Public);
    }

    public function test_submit_throws_when_entity_set_and_policy_denies(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Gate::policy(FeedItem::class, \BeegoodIT\FilamentSocialGraph\Tests\DenyFeedItemPolicy::class);

        $entity = TestUser::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse(Gate::allows('create', [FeedItem::class, $entity]));

        $this->assertCount(0, FeedItem::all());

        Livewire::test(FeedComposer::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
            ->set('body', 'Content')
            ->set('visibility', 'public')
            ->call('submit')
            ->assertForbidden();

        $this->assertCount(0, FeedItem::all());
    }
}
