<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * @deprecated For entity feed POST, prefer FeedController::store and CreateFeedItemForEntity (form POST to same URL).
 */
class FeedComposer extends Component
{
    public ?string $entityType = null;

    public ?string $entityId = null;

    public ?string $subject = null;

    public ?string $body = null;

    public string $visibility = 'public';

    protected $listeners = ['feedItemCreated' => '$refresh'];

    public function mount(?string $entityType = null, ?string $entityId = null): void
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    public function submit(): void
    {
        $this->validate([
            'body' => ['nullable', 'string', 'max:65535'],
            'subject' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'string', 'in:public,unlisted,private,followers'],
        ]);

        $entity = $this->resolveEntity();
        if ($this->entityType && $this->entityId && $entity === null) {
            throw new AuthorizationException;
        }

        $ability = config('filament-social-graph.feed_page.authorize_create_ability', 'create');
        Gate::authorize($ability, [FeedItem::class, $entity]);

        if ($entity !== null && in_array(HasSocialFeed::class, class_uses_recursive($entity), true)) {
            $feedItem = $entity->createFeedItem([
                'subject' => $this->subject ?: null,
                'body' => $this->body ?: null,
                'visibility' => Visibility::from($this->visibility),
            ]);
        } else {
            $actor = $this->resolveActor();
            if (! $actor || ! in_array(HasSocialFeed::class, class_uses_recursive($actor), true)) {
                return;
            }

            $feedItem = $actor->createFeedItem([
                'subject' => $this->subject ?: null,
                'body' => $this->body ?: null,
                'visibility' => Visibility::from($this->visibility),
            ]);
        }

        if (config('filament-social-graph.tenancy.enabled') && class_exists(\Filament\Facades\Filament::class) && $tenant = Filament::getTenant()) {
            $feedItem->update(['team_id' => $tenant->getKey()]);
        }

        $this->reset(['subject', 'body']);
        $this->dispatch('feedItemCreated');
    }

    protected function resolveActor(): ?Model
    {
        $user = auth()->user();
        if ($user) {
            return $user;
        }

        return null;
    }

    protected function resolveEntity(): ?Model
    {
        if (! $this->entityType || ! $this->entityId) {
            return null;
        }

        $entityModels = config('filament-social-graph.entity_models', []);
        $actorModels = config('filament-social-graph.actor_models', []);
        $allowed = array_merge($entityModels, $actorModels);

        if (! in_array($this->entityType, $allowed, true)) {
            return null;
        }

        $entity = $this->entityType::find($this->entityId);

        return $entity instanceof Model ? $entity : null;
    }

    public function render()
    {
        return view('filament-social-graph::livewire.feed-composer');
    }
}
