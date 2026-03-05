<div>
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    @include('filament-social-graph::feed.partials.quill-dark-mode')

    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <form wire:submit.prevent="createItem" class="space-y-4">
            <flux:field>
                <flux:label class="sr-only" for="feed-composer-subject">
                    {{ __('filament-social-graph::feed_item.subject') }}
                </flux:label>

                <flux:input
                    type="text"
                    id="feed-composer-subject"
                    wire:model.defer="subject"
                    placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                />

                @error('subject')
                    <flux:error name="subject">{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div>
                <flux:label class="sr-only">
                    {{ __('filament-social-graph::feed_item.body') }}
                </flux:label>

                <div wire:ignore>
                    <div
                    id="{{ $quillId }}_editor"
                    class="min-h-[120px] rounded-lg border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-800"
                    data-placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"
                ></div>
                </div>
                <input type="hidden" id="{{ $quillId }}_value" wire:model.defer="body">

                @error('body')
                    <flux:error name="body">{{ $message }}</flux:error>
                @enderror
            </div>

            <div>
                <flux:field>
                    <flux:label for="feed-composer-attachments">{{ __('filament-social-graph::feed_item.attachments') }}</flux:label>
                    <div
                        class="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 transition dark:border-zinc-600"
                        data-feed-drop-zone="feed-composer-attachments"
                        onclick="document.getElementById('feed-composer-attachments').click()"
                        role="button"
                        tabindex="0"
                        aria-label="{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}"
                        onkeydown="if(event.key==='Enter'||event.key===' ') { event.preventDefault(); document.getElementById('feed-composer-attachments').click(); }"
                    >
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}</span>
                    </div>
                    <input
                        type="file"
                        id="feed-composer-attachments"
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

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" size="base" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createItem">{{ __('filament-social-graph::feed.post') }}</span>
                    <span wire:loading wire:target="createItem">{{ __('filament-social-graph::feed.posting') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
@once
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
@endonce

@push('scripts')
<script>
(function () {
    function initQuill(uid) {
        const editorEl = document.getElementById(uid + '_editor');
        const hiddenEl = document.getElementById(uid + '_value');
        if (!editorEl || !hiddenEl) return;

        // Prevent double-init
        if (editorEl.__quill) return;

        const quill = new Quill(editorEl, {
            theme: 'snow',
            placeholder: editorEl.getAttribute('data-placeholder') || '',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        editorEl.__quill = quill;

        // Hydrate initial value (from Livewire)
        if (hiddenEl.value) {
            quill.clipboard.dangerouslyPasteHTML(hiddenEl.value);
        }

        const syncToLivewire = () => {
            hiddenEl.value = quill.root.innerHTML;
            hiddenEl.dispatchEvent(new Event('input', { bubbles: true }));
        };

        quill.on('text-change', syncToLivewire);

        // If Livewire updates the hidden input (e.g. reset after submit), reflect it
        const observer = new MutationObserver(() => {
            // Only update editor when Livewire changes hidden field and editor differs
            if (hiddenEl.value !== quill.root.innerHTML) {
                quill.clipboard.dangerouslyPasteHTML(hiddenEl.value || '');
            }
        });

        observer.observe(hiddenEl, { attributes: true, attributeFilter: ['value'] });

        // Store cleanup hook
        editorEl.__quillCleanup = () => observer.disconnect();
    }

    document.addEventListener('livewire:navigated', () => {
        initQuill(@json($quillId));
        console.log('livewire:navigated', @json($quillId));
    });

    document.addEventListener('livewire:updated', () => {
        initQuill(@json($quillId));
        console.log('livewire:updated', @json($quillId));
    });
})();
</script>
@endpush

@include('filament-social-graph::feed.partials.attachment-drop-zone-script')