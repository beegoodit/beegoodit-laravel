<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Actions\CreateFeedItemForEntity;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FeedCreateForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Model $entity;

    public string $subject = '';

    public string $body = '';

    public string $quillId = '';

    /**
     * @var array<int, \Illuminate\Http\UploadedFile|\Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $attachments = [];

    public function mount(Model $entity): void
    {
        $this->entity = $entity;
    }

    public function createItem(): mixed
    {
        $maxFiles = config('filament-social-graph.attachments.max_files', 5);
        $maxKb = config('filament-social-graph.attachments.max_file_size_kb', 5120);
        $mimes = config('filament-social-graph.attachments.allowed_mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);

        $this->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65535'],
            'attachments' => ['nullable', 'array', 'max:'.$maxFiles],
            'attachments.*' => ['required', 'file', 'max:'.$maxKb, 'mimes:'.implode(',', $mimes)],
        ], [
            'body.required' => __('filament-social-graph::feed_item.body_required'),
        ]);

        $ability = config('filament-social-graph.feed_page.authorize_create_ability', 'create');
        Gate::authorize($ability, [FeedItem::class, $this->entity]);

        CreateFeedItemForEntity::run($this->entity, [
            'subject' => $this->subject ?: null,
            'body' => $this->body,
            'attachments' => $this->attachments,
        ]);

        session()->flash('success', __('filament-social-graph::feed_item.created'));

        return $this->redirect(request()->header('Referer', '/'), navigate: true);
    }

    public function removeAttachment(int $index): void
    {
        if (array_key_exists($index, $this->attachments)) {
            array_splice($this->attachments, $index, 1);
        }
    }

    public function render(): View
    {
        return view('filament-social-graph::livewire.feed-create-form');
    }
}
