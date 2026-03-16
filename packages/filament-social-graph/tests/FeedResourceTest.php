<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages\CreateFeed;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages\EditFeed;
use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages\ListFeeds;
use BeegoodIT\FilamentSocialGraph\Models\Feed;

class FeedResourceTest extends TestCase
{
    public function test_resource_has_list_create_edit_pages_only_no_view(): void
    {
        $pages = FeedResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        $this->assertArrayNotHasKey('view', $pages);
        $this->assertSame(ListFeeds::class, $pages['index']->getPage());
        $this->assertSame(CreateFeed::class, $pages['create']->getPage());
        $this->assertSame(EditFeed::class, $pages['edit']->getPage());
    }

    public function test_can_create_feed_via_factory_and_resource_model_matches(): void
    {
        $team = TestTeam::create(['name' => 'T1']);
        $feed = Feed::factory()->forOwner($team)->create();

        $this->assertDatabaseHas('feeds', [
            'id' => $feed->id,
            'owner_type' => $team->getMorphClass(),
            'owner_id' => $team->getKey(),
        ]);
        $this->assertSame(Feed::class, FeedResource::getModel());
    }
}
