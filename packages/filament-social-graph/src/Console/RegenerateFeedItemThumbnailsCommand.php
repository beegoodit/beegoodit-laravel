<?php

namespace BeegoodIT\FilamentSocialGraph\Console;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService;
use Illuminate\Console\Command;

class RegenerateFeedItemThumbnailsCommand extends Command
{
    protected $signature = 'feed-items:regenerate-thumbnails
        {--id= : Only process this feed item ID (UUID)}
        {--missing-only : Only generate when thumbnail file is missing}
        {--force : Always overwrite existing thumbnails}
        {--dry-run : Report what would be done without writing}';

    protected $description = 'Regenerate thumbnails for feed item image attachments';

    public function handle(): int
    {
        $query = FeedItem::query()->withoutGlobalScopes();
        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $dryRun = (bool) $this->option('dry-run');
        $missingOnly = (bool) $this->option('missing-only');
        $force = (bool) $this->option('force');

        if ($dryRun) {
            $this->info('Dry run — no files will be written.');
        }

        $disk = FeedItem::getStorageDisk();
        $service = new FeedItemThumbnailService;
        $itemsProcessed = 0;
        $thumbsCreated = 0;
        $thumbsSkipped = 0;
        $thumbsFailed = 0;

        $query->chunkById(100, function ($feedItems) use ($disk, $service, $dryRun, $missingOnly, $force, &$itemsProcessed, &$thumbsCreated, &$thumbsSkipped, &$thumbsFailed): void {
            foreach ($feedItems as $feedItem) {
                $attachments = $feedItem->attachments ?? [];
                foreach ($attachments as $path) {
                    if (! FeedItem::isImagePath($path)) {
                        continue;
                    }
                    $thumbPath = FeedItem::getThumbnailPath($path);
                    $exists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($thumbPath);

                    if ($missingOnly && $exists) {
                        $thumbsSkipped++;

                        continue;
                    }
                    if ($force === false && $exists) {
                        $thumbsSkipped++;

                        continue;
                    }
                    if ($dryRun) {
                        $thumbsCreated++;
                        $this->line("Would generate: {$thumbPath}");

                        continue;
                    }
                    if ($service->generateThumbnail($disk, $path)) {
                        $thumbsCreated++;
                    } else {
                        $thumbsFailed++;
                        $originalExists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($path);
                        if (! $originalExists) {
                            $this->warn("Original file missing on disk [{$disk}]: {$path}");
                        }
                    }
                }
                $itemsProcessed++;
            }
        });

        $this->info("Processed {$itemsProcessed} feed item(s). Thumbnails created: {$thumbsCreated}, skipped: {$thumbsSkipped}, failed: {$thumbsFailed}.");
        if ($thumbsFailed > 0) {
            $this->warn('Check storage/logs/laravel.log for "FeedItem thumbnail" warnings. Ensure original image files exist on disk ['.$disk.'].');
        }

        return self::SUCCESS;
    }
}
