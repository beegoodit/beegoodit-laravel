<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Services\FeedItemThumbnailService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFeedItemForEntity
{
    use AsAction;

    public function handle(Model $entity, array $data): FeedItem
    {
        if (! in_array(HasSocialFeed::class, class_uses_recursive($entity), true)) {
            throw new \InvalidArgumentException(
                'Entity must use '.HasSocialFeed::class.' to create feed items.'
            );
        }

        $feedItem = $entity->createFeedItem([
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'] ?? null,
        ]);

        if (config('filament-social-graph.tenancy.enabled')) {
            $teamId = $this->resolveCurrentTeamId();
            if ($teamId !== null) {
                $feedItem->update(['team_id' => $teamId]);
            }
        }

        $paths = $this->storeAttachmentFiles($feedItem, $data['attachments'] ?? []);
        if ($paths !== []) {
            $feedItem->update(['attachments' => $paths]);
            $this->generateThumbnailsForPaths($feedItem, $paths);
        }

        return $feedItem;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    protected function storeAttachmentFiles(FeedItem $feedItem, array $files): array
    {
        if ($files === []) {
            return [];
        }

        $disk = FeedItem::getStorageDisk();
        $directory = $this->attachmentDirectory($feedItem);
        $paths = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();
            $filename = Str::uuid()->toString().($extension ? '.'.$extension : '');
            $path = $file->storeAs($directory, $filename, ['disk' => $disk]);
            if ($path !== false) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    protected function attachmentDirectory(FeedItem $feedItem): string
    {
        $teamId = $feedItem->team_id ?? $this->resolveCurrentTeamId();

        return FeedItem::getAttachmentDirectory(is_scalar($teamId) ? (string) $teamId : null);
    }

    protected function resolveCurrentTeamId(): mixed
    {
        $resolver = config('filament-social-graph.tenancy.team_resolver');
        $team = null;
        if ($resolver !== null && is_callable($resolver)) {
            $team = $resolver();
        }
        if ($team === null && class_exists(Filament::class) && app()->bound('filament')) {
            $team = Filament::getTenant();
        }

        return $team instanceof Model ? $team->getKey() : (is_scalar($team) ? $team : null);
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function generateThumbnailsForPaths(FeedItem $feedItem, array $paths): void
    {
        $disk = FeedItem::getStorageDisk();
        $service = new FeedItemThumbnailService;
        foreach ($paths as $path) {
            if (FeedItem::isImagePath($path)) {
                $service->generateThumbnail($disk, $path);
            }
        }
    }
}
