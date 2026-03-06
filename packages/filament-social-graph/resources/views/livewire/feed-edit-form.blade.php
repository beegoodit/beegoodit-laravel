<div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
    <form wire:submit="updateItem" class="space-y-4">
        <div>
            <flux:field>
                <flux:label class="sr-only" for="feed-edit-subject">{{ __('filament-social-graph::feed_item.subject') }}</flux:label>
                <flux:input
                    type="text"
                    id="feed-edit-subject"
                    wire:model="subject"
                    placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                />
            </flux:field>
        </div>
        <div>
            <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.body') }}</flux:label>
            <div wire:ignore>
                <div id="feed-edit-body-editor" class="min-h-[100px] rounded-lg border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-800" data-placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"></div>
            </div>
            <input type="hidden" id="feed-edit-body" wire:model="body">
            @error('body')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </div>
        <div>
            @if(count($this->existingPaths) > 0)
                <flux:field>
                    <flux:label>{{ __('filament-social-graph::feed_item.attachments') }}</flux:label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($existingPaths as $path)
                            @php
                                $url = Storage::disk($editDisk)->url($path);
                                $filename = basename($path);
                                $isImage = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::isImagePath($path);
                                $marked = in_array($path, $attachmentsRemove, true);
                            @endphp
                            <div class="grid grid-cols-1 grid-rows-1 items-start gap-2 rounded border border-zinc-200 p-2 dark:border-zinc-600 {{ $marked ? 'opacity-50' : '' }}">
                                @if($isImage)
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="block shrink-0">
                                        <img src="{{ $url }}" alt="{{ $filename }}" class="aspect-3/2 max-h-24 rounded object-cover">
                                    </a>
                                @else
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="text-sm text-zinc-600 dark:text-zinc-400">{{ $filename }}</a>
                                @endif
                                @if($marked)
                                    <button type="button" wire:click="unmarkAttachmentForRemoval({{ json_encode($path) }})" class="text-sm text-primary-600 dark:text-primary-400">{{ __('filament-social-graph::feed.keep') }}</button>
                                @else
                                    <button type="button" wire:click="markAttachmentForRemoval({{ json_encode($path) }})" class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">{{ __('filament-social-graph::feed_item.attachments_remove') }}</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </flux:field>
            @endif
        </div>

        <div>
            <flux:field>
                <flux:label for="feed-edit-attachments">{{ __('filament-social-graph::feed_item.attachments_new') }}</flux:label>
                <div
                    class="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 transition dark:border-zinc-600"
                    data-feed-drop-zone="feed-edit-attachments"
                    onclick="document.getElementById('feed-edit-attachments').click()"
                    role="button"
                    tabindex="0"
                    aria-label="{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}"
                    onkeydown="if(event.key==='Enter'||event.key===' ') { event.preventDefault(); document.getElementById('feed-edit-attachments').click(); }"
                >
                    <span class="text-center text-sm text-zinc-600 dark:text-zinc-400">{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}</span>
                </div>
                <input
                    type="file"
                    id="feed-edit-attachments"
                    wire:model="attachments"
                    class="sr-only"
                    multiple
                    accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                >
                <flux:description>{{ __('filament-social-graph::feed_item.attachments_hint', ['max_files' => config('filament-social-graph.attachments.max_files', 5), 'max_mb' => (int) (config('filament-social-graph.attachments.max_file_size_kb', 5120) / 1024)]) }}</flux:description>
                @error('attachments')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
                @error('attachments.*')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
                @if(count($attachments) > 0)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($attachments as $index => $file)
                            <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-600 dark:bg-zinc-700">
                                <span class="max-w-[8rem] truncate text-sm text-zinc-700 dark:text-zinc-300">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200" aria-label="{{ __('filament-social-graph::feed_item.attachments_remove') }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:field>
        </div>
        <div class="flex justify-between">
            <a href="{{ $feedUrl }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                {{ __('filament-social-graph::feed.cancel') }}
            </a>
            <flux:button type="submit" variant="primary" size="base" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateItem">{{ __('filament-social-graph::feed.update') }}</span>
                <span wire:loading wire:target="updateItem">{{ __('filament-social-graph::feed.updating') ?? 'Updating…' }}</span>
            </flux:button>
        </div>
    </form>
</div>

@include('filament-social-graph::feed.partials.attachment-drop-zone-script')
@push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    @include('filament-social-graph::feed.partials.quill-dark-mode')
@endpush
@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editorEl = document.getElementById('feed-edit-body-editor');
            var hiddenEl = document.getElementById('feed-edit-body');
            if (!editorEl || !hiddenEl) return;
            var quill = new Quill(editorEl, {
                theme: 'snow',
                placeholder: editorEl.getAttribute('data-placeholder') || '',
            });
            if (hiddenEl.value) quill.root.innerHTML = hiddenEl.value;
            quill.on('text-change', function() {
                hiddenEl.value = quill.root.innerHTML;
                hiddenEl.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    </script>
@endpush
