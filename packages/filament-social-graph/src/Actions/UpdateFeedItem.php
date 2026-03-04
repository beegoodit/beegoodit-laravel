<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateFeedItem
{
    use AsAction;

    public function handle(FeedItem $feedItem, array $data): FeedItem
    {
        $visibility = $data['visibility'] ?? $feedItem->visibility;
        if (! $visibility instanceof Visibility) {
            $visibility = Visibility::from($visibility);
        }

        $currentPaths = $feedItem->attachments ?? [];
        $toRemove = $this->validRemovalPaths($feedItem, $data['attachments_remove'] ?? []);
        $keptPaths = array_values(array_diff($currentPaths, $toRemove));

        $this->deleteAttachmentFiles($feedItem, $toRemove);

        $newPaths = $this->storeNewAttachmentFiles($feedItem, $data['attachments'] ?? []);
        $maxFiles = config('filament-social-graph.attachments.max_files', 5);
        if (count($keptPaths) + count($newPaths) > $maxFiles) {
            throw ValidationException::withMessages([
                'attachments' => [__('filament-social-graph::feed_item.attachments_max_files', ['max' => $maxFiles])],
            ]);
        }

        $feedItem->update([
            'subject' => $data['subject'] ?? $feedItem->subject,
            'body' => $data['body'] ?? $feedItem->body,
            'visibility' => $visibility,
            'attachments' => array_merge($keptPaths, $newPaths),
        ]);

        return $feedItem;
    }

    /**
     * @param  array<int, string>  $requestedPaths
     * @return array<int, string>
     */
    protected function validRemovalPaths(FeedItem $feedItem, array $requestedPaths): array
    {
        $current = $feedItem->attachments ?? [];

        return array_values(array_intersect($requestedPaths, $current));
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function deleteAttachmentFiles(FeedItem $feedItem, array $paths): void
    {
        if ($paths === []) {
            return;
        }
        $disk = FeedItem::getStorageDisk();
        foreach ($paths as $path) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    protected function storeNewAttachmentFiles(FeedItem $feedItem, array $files): array
    {
        if ($files === []) {
            return [];
        }
        $disk = FeedItem::getStorageDisk();
        $directory = FeedItem::getAttachmentDirectory($feedItem->team_id ? (string) $feedItem->team_id : null);
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
}
