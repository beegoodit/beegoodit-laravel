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

    public function render()
    {
        return view('filament-social-graph::livewire.feed-item-card');
    }
}
