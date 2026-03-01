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
}
