<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Actions\UpdateFeedItem;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FeedEditForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public FeedItem $feedItem;

    public string $feedUrl;

    public string $subject = '';

    public string $body = '';

    /**
     * @var array<int, \Illuminate\Http\UploadedFile|\Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $attachments = [];

    /**
     * @var array<int, string>
     */
    public array $attachmentsRemove = [];

    public function mount(FeedItem $feedItem, string $feedUrl): void
    {
        $this->feedItem = $feedItem;
        $this->feedUrl = $feedUrl;
        $this->subject = $feedItem->subject ?? '';
        $this->body = $feedItem->body ?? '';
    }

    public function updateItem(): mixed
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

        Gate::authorize('update', $this->feedItem);

        UpdateFeedItem::run($this->feedItem, [
            'subject' => $this->subject ?: null,
            'body' => $this->body,
            'attachments' => $this->attachments,
            'attachments_remove' => $this->attachmentsRemove,
        ]);

        session()->flash('success', __('filament-social-graph::feed_item.updated'));

        return $this->redirect($this->feedUrl, navigate: true);
    }

    public function removeAttachment(int $index): void
    {
        if (array_key_exists($index, $this->attachments)) {
            array_splice($this->attachments, $index, 1);
        }
    }

    public function markAttachmentForRemoval(string $path): void
    {
        if (! in_array($path, $this->attachmentsRemove, true)) {
            $this->attachmentsRemove[] = $path;
        }
    }

    public function unmarkAttachmentForRemoval(string $path): void
    {
        $this->attachmentsRemove = array_values(array_filter(
            $this->attachmentsRemove,
            fn (string $p): bool => $p !== $path
        ));
    }

    public function render(): View
    {
        return view('filament-social-graph::livewire.feed-edit-form');
    }
}
