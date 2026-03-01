<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use Livewire\Component;

class FeedPage extends Component
{
    public function render()
    {
        $layout = config('filament-social-graph.feed_page.layout', 'filament-social-graph::layouts.app');
        $showComposer = config('filament-social-graph.feed_page.show_composer', true);

        return view('filament-social-graph::livewire.feed-page', [
            'showComposer' => $showComposer,
        ])
            ->layout($layout);
    }
}
