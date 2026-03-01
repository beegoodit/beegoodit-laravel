<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * @deprecated Prefer FeedController (GET index + POST store) and CreateFeedItemForEntity for entity feed pages.
 */
class EntityFeedPage extends Component
{
    public Model $entity;

    public function mount(Model $entity): void
    {
        $this->entity = $entity;
        session([
            'filament_social_graph.feed_entity' => [$entity->getMorphClass(), $entity->getKey()],
        ]);
    }

    public function render()
    {
        $layout = config('filament-social-graph.feed_page.layout', 'filament-social-graph::layouts.app');
        $ability = config('filament-social-graph.feed_page.authorize_create_ability', 'create');
        $showComposer = Gate::allows($ability, [FeedItem::class, $this->entity]);

        return view('filament-social-graph::livewire.entity-feed-page', [
            'showComposer' => $showComposer,
        ])
            ->layout($layout);
    }
}
