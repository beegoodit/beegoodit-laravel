<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Support\FeedItemPlainDescription;
use Livewire\Component;

class FeedItemCard extends Component
{
    public FeedItem $feedItem;

    public ?string $editRouteName = null;

    public ?string $destroyRouteName = null;

    /** @var array<string, mixed> */
    public array $editRouteParams = [];

    /** @var array<string, mixed> */
    public array $destroyRouteParams = [];

    public ?string $showRouteName = null;

    /** @var array<string, mixed> */
    public array $showRouteParams = [];

    /**
     * When true, single-image grids use full content width (e.g. branded permalink beside info layout).
     */
    public bool $fullWidthImageGrid = false;

    /**
     * @param  array<string, mixed>  $editRouteParams
     * @param  array<string, mixed>  $destroyRouteParams
     * @param  array<string, mixed>  $showRouteParams
     */
    public function mount(
        FeedItem $feedItem,
        ?string $editRouteName = null,
        ?string $destroyRouteName = null,
        array $editRouteParams = [],
        array $destroyRouteParams = [],
        ?string $showRouteName = null,
        array $showRouteParams = [],
        bool $fullWidthImageGrid = false,
    ): void {
        $this->feedItem = $feedItem;
        $this->editRouteName = $editRouteName;
        $this->destroyRouteName = $destroyRouteName;
        $this->editRouteParams = $editRouteParams;
        $this->destroyRouteParams = $destroyRouteParams;
        $this->showRouteName = $showRouteName;
        $this->showRouteParams = $showRouteParams;
        $this->fullWidthImageGrid = $fullWidthImageGrid;
    }

    public function getEditUrl(): ?string
    {
        if ($this->editRouteName === null || $this->editRouteName === '') {
            return null;
        }

        return route($this->editRouteName, array_merge($this->editRouteParams, ['feedItem' => $this->feedItem]));
    }

    public function getDestroyUrl(): ?string
    {
        if ($this->destroyRouteName === null || $this->destroyRouteName === '') {
            return null;
        }

        return route($this->destroyRouteName, array_merge($this->destroyRouteParams, ['feedItem' => $this->feedItem]));
    }

    public function getShowUrl(): ?string
    {
        if ($this->showRouteName === null || $this->showRouteName === '') {
            return null;
        }

        return route($this->showRouteName, array_merge($this->showRouteParams, ['feedItem' => $this->feedItem]));
    }

    /**
     * @return array<int, array{path: string, url: string, thumbnail_url: string, filename: string}>
     */
    public function getImageEntries(): array
    {
        $paths = $this->getImagePaths();
        $entries = [];
        foreach ($paths as $path) {
            $entries[] = [
                'path' => $path,
                'url' => FeedItem::getAttachmentUrl($path),
                'thumbnail_url' => FeedItem::getThumbnailUrl($path),
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
            return $this->fullWidthImageGrid
                ? 'grid w-full max-w-none grid-cols-1'
                : 'grid grid-cols-1 max-w-2xl';
        }
        if ($count <= 4) {
            return 'grid grid-cols-2 gap-2';
        }

        return 'grid grid-cols-2 sm:grid-cols-3 gap-2';
    }

    public function render()
    {
        $feedItem = $this->feedItem;

        return view('filament-social-graph::livewire.feed-item-card', [
            'imageEntries' => $this->getImageEntries(),
            'fileEntries' => $this->getFileEntries(),
            'imageGridClass' => $this->getImageGridClass(),
            'editUrl' => $this->getEditUrl(),
            'destroyUrl' => $this->getDestroyUrl(),
            'showUrl' => $this->getShowUrl(),
            'feedItemShareTitle' => filled($feedItem->subject)
                ? $feedItem->subject
                : __('filament-social-graph::feed.show_title'),
            'feedItemShareDescription' => FeedItemPlainDescription::for($feedItem),
        ]);
    }
}
