<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Gate;

class FeedItemPolicyTest extends TestCase
{
    public function test_guest_cannot_create_feed_item(): void
    {
        $this->assertGuest();

        $this->assertFalse(Gate::allows('create', [FeedItem::class, null]));
    }

    public function test_authenticated_user_can_create_for_global_feed(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $this->assertTrue(Gate::allows('create', [FeedItem::class, null]));
    }

    public function test_authenticated_user_can_create_for_entity_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $entity = TestUser::create([
            'name' => 'Entity',
            'email' => 'entity@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.actor_models', [TestUser::class]);

        $this->assertTrue(Gate::allows('create', [FeedItem::class, $entity]));
    }

    public function test_authenticated_user_cannot_create_for_entity_not_in_actor_models(): void
    {
        $user = TestUser::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $entity = TestUser::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        config()->set('filament-social-graph.actor_models', []);

        $this->assertFalse(Gate::allows('create', [FeedItem::class, $entity]));
    }
}
