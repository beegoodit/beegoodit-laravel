<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService;
use Illuminate\Support\Facades\Storage;

class FeedItemThumbnailServiceTest extends TestCase
{
    private FeedItemThumbnailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config()->set('filament-social-graph.attachments.thumbnails.width', 100);
        config()->set('filament-social-graph.attachments.thumbnails.height', 100);
        config()->set('filament-social-graph.attachments.thumbnails.quality', 85);
        $this->service = new FeedItemThumbnailService;
    }

    public function test_generate_thumbnail_returns_false_for_non_image_path(): void
    {
        $path = 'feed-item-attachments/doc.pdf';
        Storage::disk('public')->put($path, 'not an image');

        $result = $this->service->generateThumbnail('public', $path);

        $this->assertFalse($result);
        $this->assertFalse(Storage::disk('public')->exists(FeedItem::getThumbnailPath($path)));
    }

    public function test_generate_thumbnail_creates_thumbnail_file_for_image(): void
    {
        $path = 'feed-item-attachments/photo.jpg';
        $imageContent = $this->createMinimalJpeg();
        Storage::disk('public')->put($path, $imageContent);

        $result = $this->service->generateThumbnail('public', $path);

        $this->assertTrue($result);
        $thumbPath = FeedItem::getThumbnailPath($path);
        $this->assertTrue(Storage::disk('public')->exists($thumbPath));
        $thumbContent = Storage::disk('public')->get($thumbPath);
        $this->assertNotEmpty($thumbContent);
    }

    public function test_generate_thumbnail_returns_false_when_original_missing(): void
    {
        $path = 'feed-item-attachments/missing.jpg';

        $result = $this->service->generateThumbnail('public', $path);

        $this->assertFalse($result);
    }

    /**
     * Create a minimal valid JPEG (small image) for testing.
     */
    private function createMinimalJpeg(): string
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            $this->markTestSkipped('Intervention Image not installed');
        }

        $driver = class_exists(\Intervention\Image\Drivers\Gd\Driver::class)
            ? new \Intervention\Image\Drivers\Gd\Driver
            : new \Intervention\Image\Drivers\Imagick\Driver;
        $manager = new \Intervention\Image\ImageManager($driver);
        $image = $manager->create(10, 10)->fill('ccc');

        return (string) $image->encodeByExtension('jpg', quality: 85);
    }
}
