<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Livewire\Component;

class FeedItemCard extends Component
{
    public FeedItem $feedItem;

    public function mount(FeedItem $feedItem): void
    {
        $this->feedItem = $feedItem;
    }

    /**
     * @return array<int, array{path: string, url: string, filename: string}>
     */
    public function getImageEntries(): array
    {
        $paths = $this->getImagePaths();
        $entries = [];
        foreach ($paths as $path) {
            $entries[] = [
                'path' => $path,
                'url' => FeedItem::getAttachmentUrl($path),
                'filename' => basename($path),
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{path: string, url: string, filename: string}>
     */
    public function getFileEntries(): array
    {
        $paths = $this->getFilePaths();
        $entries = [];
        foreach ($paths as $path) {
            $entries[] = [
                'path' => $path,
                'url' => FeedItem::getAttachmentUrl($path),
                'filename' => basename($path),
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, string>
     */
    public function getImagePaths(): array
    {
        $attachments = $this->feedItem->attachments ?? [];

        return array_values(array_filter($attachments, FeedItem::isImagePath(...)));
    }

    /**
     * @return array<int, string>
     */
    public function getFilePaths(): array
    {
        $attachments = $this->feedItem->attachments ?? [];

        return array_values(array_filter($attachments, fn (string $path): bool => ! FeedItem::isImagePath($path)));
    }

    public function getImageGridClass(): string
    {
        $count = count($this->getImagePaths());
        if ($count <= 1) {
            return 'grid grid-cols-1 max-w-2xl';
        }
        if ($count <= 4) {
            return 'grid grid-cols-2 gap-2';
        }

        return 'grid grid-cols-2 sm:grid-cols-3 gap-2';
    }

    public function render()
    {
        return view('filament-social-graph::livewire.feed-item-card', [
            'imageEntries' => $this->getImageEntries(),
            'fileEntries' => $this->getFileEntries(),
            'imageGridClass' => $this->getImageGridClass(),
        ]);
    }
}
