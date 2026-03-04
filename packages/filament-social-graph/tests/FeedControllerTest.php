<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Http\Controllers\FeedController;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class FeedControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('filament-social-graph.actor_models', [TestUser::class]);

        Route::model('entity', TestUser::class);
        Route::middleware('web')->group(function (): void {
            Route::get('feed/{entity}', [FeedController::class, 'index'])->name('feed.index');
            Route::post('feed/{entity}', [FeedController::class, 'store'])->name('feed.store');
            Route::get('feed/{entity}/items/{feedItem}/edit', [FeedController::class, 'edit'])->name('feed.items.edit');
            Route::put('feed/{entity}/items/{feedItem}', [FeedController::class, 'update'])->name('feed.items.update');
            Route::delete('feed/{entity}/items/{feedItem}', [FeedController::class, 'destroy'])->name('feed.items.destroy');
        });
    }

    public function test_index_returns_200_and_shows_feed_when_allowed(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('feed.index', ['entity' => $user->getKey()]));

        $response->assertOk();
        $response->assertSee(__('filament-social-graph::feed.title'), false);
    }

    public function test_store_creates_feed_item_and_redirects_back(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);
        $this->assertCount(0, FeedItem::all());

        $response = $this->post(route('feed.store', ['entity' => $user->getKey()]), [
            'body' => 'Hello from controller',
            'subject' => 'Test subject',
            'visibility' => Visibility::Public->value,
        ]);

        $response->assertRedirect();
        $this->assertCount(1, FeedItem::all());
        $item = FeedItem::first();
        $this->assertSame('Hello from controller', $item->body);
        $this->assertSame('Test subject', $item->subject);
        $this->assertSame($user->getKey(), $item->actor_id);
    }

    public function test_index_hides_composer_when_policy_denies(): void
    {
        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('feed.index', ['entity' => $user->getKey()]));

        $response->assertOk();
        $response->assertDontSee(__('filament-social-graph::feed.composer_placeholder'), false);
    }

    public function test_store_returns_403_when_policy_denies(): void
    {
        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('feed.store', ['entity' => $user->getKey()]), [
            'body' => 'Hello',
            'visibility' => Visibility::Public->value,
        ]);

        $response->assertForbidden();
        $this->assertCount(0, FeedItem::all());
    }

    public function test_edit_returns_200_and_form_when_allowed(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => 'Original',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('feed.items.edit', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]));

        $response->assertOk();
        $response->assertSee(__('filament-social-graph::feed.edit_title'), false);
    }

    public function test_edit_returns_403_when_policy_denies(): void
    {
        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => 'Original',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('feed.items.edit', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]));

        $response->assertForbidden();
    }

    public function test_edit_returns_404_when_feed_item_not_in_scope(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $otherUser = TestUser::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $otherUser->getKey(),
            'body' => 'Other post',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('feed.items.edit', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]));

        $response->assertNotFound();
    }

    public function test_update_modifies_feed_item_and_redirects(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'subject' => 'Old',
            'body' => 'Old body',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('feed.items.update', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]), [
            'subject' => 'New subject',
            'body' => 'New body',
            'visibility' => Visibility::Private->value,
        ]);

        $response->assertRedirect();
        $feedItem->refresh();
        $this->assertSame('New subject', $feedItem->subject);
        $this->assertSame('New body', $feedItem->body);
        $this->assertSame(Visibility::Private, $feedItem->visibility);
    }

    public function test_update_returns_403_when_policy_denies(): void
    {
        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => 'Original',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('feed.items.update', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]), [
            'body' => 'Updated',
            'visibility' => Visibility::Public->value,
        ]);

        $response->assertForbidden();
        $this->assertSame('Original', $feedItem->fresh()->body);
    }

    public function test_destroy_deletes_feed_item_and_redirects(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => 'To delete',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('feed.items.destroy', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]));

        $response->assertRedirect();
        $this->assertNull(FeedItem::find($feedItem->id));
    }

    public function test_destroy_returns_403_when_policy_denies(): void
    {
        Gate::policy(FeedItem::class, DenyFeedItemPolicy::class);

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => 'Keep',
            'visibility' => Visibility::Public,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('feed.items.destroy', ['entity' => $user->getKey(), 'feedItem' => $feedItem->id]));

        $response->assertForbidden();
        $this->assertNotNull(FeedItem::find($feedItem->id));
    }
}
