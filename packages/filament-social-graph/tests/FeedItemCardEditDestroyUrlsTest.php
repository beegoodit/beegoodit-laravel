<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedItemCard;
use BeegoodIT\FilamentSocialGraph\Livewire\FeedList;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class FeedItemCardEditDestroyUrlsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        $locale = 'en';
        Route::get('/feed/items/{feedItem}/edit', fn () => null)->name("{$locale}.platform.feed.items.edit");
        Route::delete('/feed/items/{feedItem}', fn () => null)->name("{$locale}.platform.feed.items.destroy");
    }

    public function test_feed_item_card_shows_edit_and_destroy_links_when_route_props_passed(): void
    {
        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post',
        ]);

        $editUrl = route('en.platform.feed.items.edit', ['feedItem' => $feedItem]);
        $destroyUrl = route('en.platform.feed.items.destroy', ['feedItem' => $feedItem]);

        Livewire::actingAs($user);
        $component = Livewire::test(FeedItemCard::class, [
            'feedItem' => $feedItem,
            'editRouteName' => 'en.platform.feed.items.edit',
            'destroyRouteName' => 'en.platform.feed.items.destroy',
            'editRouteParams' => [],
            'destroyRouteParams' => [],
        ]);

        $component->assertSee($editUrl, false);
        $component->assertSee($destroyUrl, false);
        $component->assertSee(__('filament-social-graph::feed.edit'), false);
    }

    public function test_feed_item_card_hides_edit_and_destroy_when_route_props_not_passed(): void
    {
        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster2@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post',
        ]);

        Livewire::actingAs($user);
        $html = Livewire::test(FeedItemCard::class, ['feedItem' => $feedItem])
            ->assertDontSee(__('filament-social-graph::feed.edit'), false)
            ->html();
    }

    public function test_feed_list_passes_route_props_to_cards(): void
    {
        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster3@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post',
        ]);

        $editUrl = route('en.platform.feed.items.edit', ['feedItem' => $feedItem]);

        Livewire::actingAs($user);
        Livewire::test(FeedList::class, [
            'feedId' => $feedItem->feed_id,
            'editRouteName' => 'en.platform.feed.items.edit',
            'destroyRouteName' => 'en.platform.feed.items.destroy',
            'editRouteParams' => [],
            'destroyRouteParams' => [],
        ])
            ->assertSee($editUrl, false);
    }
}
