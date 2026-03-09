<?php

namespace BeegoodIT\FilamentSocialGraph\Services;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class FeedItemThumbnailService
{
    public function generateThumbnail(string $disk, string $originalPath): bool
    {
        if (! FeedItem::isImagePath($originalPath)) {
            return false;
        }

        $content = Storage::disk($disk)->get($originalPath);
        if ($content === null) {
            return false;
        }

        try {
            $manager = $this->imageManager();
            $image = $manager->read($content);
        } catch (\Throwable $e) {
            Log::warning('FeedItem thumbnail: could not read image.', [
                'path' => $originalPath,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        $width = (int) config('filament-social-graph.attachments.thumbnails.width', 400);
        $height = (int) config('filament-social-graph.attachments.thumbnails.height', 400);
        $quality = (int) config('filament-social-graph.attachments.thumbnails.quality', 85);

        $image->cover($width, $height);

        $thumbPath = FeedItem::getThumbnailPath($originalPath);
        $extension = strtolower(pathinfo($originalPath, PATHINFO_EXTENSION));

        try {
            $encoded = $image->encodeByExtension($extension, quality: $quality);
            Storage::disk($disk)->put($thumbPath, (string) $encoded);
        } catch (\Throwable $e) {
            Log::warning('FeedItem thumbnail: could not encode or save.', [
                'path' => $thumbPath,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    private function imageManager(): ImageManager
    {
        $driver = $this->resolveDriver();

        return new ImageManager($driver);
    }

    /**
     * Prefer GD; fall back to Imagick if GD is not available.
     */
    private function resolveDriver(): GdDriver|ImagickDriver
    {
        if (extension_loaded('gd')) {
            return new GdDriver;
        }

        if (extension_loaded('imagick')) {
            return new ImagickDriver;
        }

        throw new \RuntimeException('FeedItem thumbnails require GD or Imagick extension.');
    }
}
