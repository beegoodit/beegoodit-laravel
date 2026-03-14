<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Livewire\FeedItemCard;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class FeedItemCardImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_single_image_renders_with_lazy_loading_and_data_lightbox(): void
    {
        $path = 'feed-item-attachments/photo.jpg';
        Storage::disk('public')->put($path, 'image content');

        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post with one image',
            'attachments' => [$path],
        ]);

        Livewire::test(FeedItemCard::class, ['feedItem' => $feedItem])
            ->assertSee('loading="lazy"', false)
            ->assertSee('data-lightbox', false);
    }

    public function test_multiple_images_render_in_grid(): void
    {
        $paths = [
            'feed-item-attachments/a.jpg',
            'feed-item-attachments/b.png',
        ];
        foreach ($paths as $p) {
            Storage::disk('public')->put($p, 'content');
        }

        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post with two images',
            'attachments' => $paths,
        ]);

        $html = Livewire::test(FeedItemCard::class, ['feedItem' => $feedItem])
            ->html();

        $this->assertStringContainsString('grid', $html);
        $this->assertStringContainsString('data-lightbox-group', $html);
    }

    public function test_images_and_non_image_attachments_rendered_separately(): void
    {
        $imagePath = 'feed-item-attachments/photo.jpg';
        $filePath = 'feed-item-attachments/doc.pdf';
        Storage::disk('public')->put($imagePath, 'content');
        Storage::disk('public')->put($filePath, 'content');

        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Mixed attachments',
            'attachments' => [$imagePath, $filePath],
        ]);

        Livewire::test(FeedItemCard::class, ['feedItem' => $feedItem])
            ->assertSee('doc.pdf', false);
    }

    public function test_image_links_have_href(): void
    {
        $path = 'feed-item-attachments/photo.jpg';
        Storage::disk('public')->put($path, 'content');

        $user = TestUser::create([
            'name' => 'Poster',
            'email' => 'poster@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'feed_id' => \BeegoodIT\FilamentSocialGraph\Models\Feed::firstOrCreateForOwner($user)->getKey(),
            'body' => 'Post',
            'attachments' => [$path],
        ]);

        $html = Livewire::test(FeedItemCard::class, ['feedItem' => $feedItem])
            ->html();

        $this->assertStringContainsString('data-lightbox', $html);
        $this->assertStringContainsString('data-lightbox-group', $html);
        $this->assertMatchesRegularExpression('/<a[^>]*data-lightbox[^>]*href="[^"]*photo\.jpg/', $html);
    }
}
