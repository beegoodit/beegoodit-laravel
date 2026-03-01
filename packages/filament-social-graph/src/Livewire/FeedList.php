<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class FeedList extends Component
{
    use WithPagination;

    public ?string $entityType = null;

    public ?string $entityId = null;

    public int $perPage = 20;

    public function mount(?string $entityType = null, ?string $entityId = null): void
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    public function getFeedItems(): LengthAwarePaginator
    {
        $query = FeedItem::query()
            ->with(['actor', 'attachments'])
            ->orderByDesc('created_at');

        if ($this->entityType && $this->entityId) {
            $query->where(function (Builder $q): void {
                $q->where('actor_type', $this->entityType)
                    ->where('actor_id', $this->entityId);
            });
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
