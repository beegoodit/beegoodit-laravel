<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\WithFileUploads;

class FeedComposer extends Component
{
    use WithFileUploads;

    public ?string $entityType = null;

    public ?string $entityId = null;

    public ?string $subject = null;

    public ?string $body = null;

    public string $visibility = 'public';

    public array $attachments = [];

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

        $actor = $this->resolveActor();
        if (! $actor || ! in_array(HasSocialFeed::class, class_uses_recursive($actor), true)) {
            return;
        }

        $feedItem = $actor->createFeedItem([
            'subject' => $this->subject ?: null,
            'body' => $this->body ?: null,
            'visibility' => Visibility::from($this->visibility),
        ]);

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

    public function render()
    {
        return view('filament-social-graph::livewire.feed-composer');
    }
}
