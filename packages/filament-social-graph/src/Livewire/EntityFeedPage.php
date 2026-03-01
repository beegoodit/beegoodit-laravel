<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class EntityFeedPage extends Component
{
    public Model $entity;

    public function mount(Model $entity): void
    {
        $this->entity = $entity;
    }

    public function render()
    {
        $layout = config('filament-social-graph.feed_page.layout', 'filament-social-graph::layouts.app');
        $showComposer = config('filament-social-graph.feed_page.show_composer_on_entity_feed', true);

        return view('filament-social-graph::livewire.entity-feed-page', [
            'showComposer' => $showComposer,
        ])
            ->layout($layout);
    }
}
