<?php

namespace BeegoodIT\FilamentSocialGraph\Http\Controllers;

use BeegoodIT\FilamentSocialGraph\Actions\CreateFeedItemForEntity;
use BeegoodIT\FilamentSocialGraph\Http\Requests\StoreFeedItemRequest;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeedController
{
    public function index(Request $request): View
    {
        $entity = $this->entityFromRoute($request);
        $layout = config('filament-social-graph.feed_page.layout', 'filament-social-graph::layouts.app');
        $ability = config('filament-social-graph.feed_page.authorize_create_ability', 'create');
        $showComposer = Gate::allows($ability, [FeedItem::class, $entity]);

        $title = ($entity->name ?? class_basename($entity)).' - '.__('filament-social-graph::feed.title');
        $viewName = config('filament-social-graph.feed_page.index_view') ?: 'filament-social-graph::feed.index';
        $data = [
            'entity' => $entity,
            'showComposer' => $showComposer,
            'layout' => $layout,
            'title' => $title,
        ];

        return view($viewName, $data);
    }

    public function store(StoreFeedItemRequest $request): RedirectResponse
    {
        $entity = $this->entityFromRoute($request);
        $ability = config('filament-social-graph.feed_page.authorize_create_ability', 'create');
        Gate::authorize($ability, [FeedItem::class, $entity]);

        $action = new CreateFeedItemForEntity;
        $action($entity, $request->validated());

        return redirect()->back()->with('success', __('filament-social-graph::feed_item.created'));
    }

    /**
     * Resolve the feed entity from the route (e.g. bound as {team} or {entity} by the app).
     */
    protected function entityFromRoute(Request $request): Model
    {
        $route = $request->route();
        $entity = $route->parameter('team') ?? $route->parameter('entity')
            ?? collect($route->parameters())->first(fn (mixed $p): bool => $p instanceof Model);

        if ($entity instanceof Model) {
            return $entity;
        }

        $entityModels = config('filament-social-graph.entity_models', []);
        foreach (['team', 'entity'] as $key) {
            $value = $route->parameter($key);
            if (is_string($value) && $entityModels !== []) {
                $modelClass = $entityModels[0];
                $instance = new $modelClass;
                $routeKey = $instance->getRouteKeyName();
                $query = $modelClass::query();
                if ($routeKey !== 'slug' && in_array('slug', $instance->getFillable(), true)) {
                    $query->where('slug', $value);
                } else {
                    $query->where($routeKey, $value);
                }
                $model = $query->firstOrFail();

                return $model;
            }
        }

        throw new \InvalidArgumentException('Feed route must bind an Eloquent model (e.g. team, entity).');
    }
}
