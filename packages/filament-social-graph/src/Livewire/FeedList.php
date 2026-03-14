<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class FeedList extends Component
{
    use WithPagination;

    public ?string $feedId = null;

    /** @deprecated Use feedId instead. Kept for backward compatibility when view passes entityType/entityId. */
    public ?string $entityType = null;

    /** @deprecated Use feedId instead. Kept for backward compatibility when view passes entityType/entityId. */
    public ?string $entityId = null;

    public int $perPage = 20;

    public function mount(?string $feedId = null, ?string $entityType = null, ?string $entityId = null): void
    {
        $this->feedId = $feedId;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    public function getFeedItems(): LengthAwarePaginator
    {
        $query = FeedItem::query()
            ->with(['feed.owner'])
            ->latest();

        if ($this->feedId !== null && $this->feedId !== '') {
            $query->where('feed_id', $this->feedId);
        } elseif ($this->entityType && $this->entityId) {
            $query->whereHas('feed', fn ($q) => $q->where('owner_type', $this->entityType)->where('owner_id', $this->entityId));
        }

        if (config('filament-social-graph.tenancy.enabled') && function_exists('filament') && $tenant = filament()->getTenant()) {
            $query->where('team_id', $tenant->getKey());
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('filament-social-graph::livewire.feed-list', [
            'feedItems' => $this->getFeedItems(),
        ]);
    }
}
