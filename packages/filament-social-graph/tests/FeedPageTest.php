<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedList;
use Livewire\Livewire;

class FeedPageTest extends TestCase
{
    public function test_feed_list_shows_empty_state_when_no_items(): void
    {
        Livewire::test(FeedList::class)
            ->assertSee(__('filament-social-graph::feed.no_items'));
    }

    public function test_feed_list_shows_feed_items(): void
    {
        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->createFeedItem([
            'subject' => 'First Post',
            'body' => 'Content here',
        ]);

        Livewire::test(FeedList::class)
            ->assertSee('First Post')
            ->assertSee('Content here')
            ->assertSee('Poster');
    }
}
