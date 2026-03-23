@once
@push('styles')
<style>.feed-composer-expandable[x-cloak]{display:none !important}</style>
@endpush
@endonce
<div>
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    @include('filament-social-graph::feed.partials.quill-dark-mode')

    <div
        class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
        x-data="{
            expanded: false,
            dropZoneActive: false,
            uploadingCount: 0,
            maxFiles: @json(config('filament-social-graph.attachments.max_files', 5)),
            handleDrop(e) {
                if (typeof $wire === 'undefined') return;
                e.preventDefault();
                e.stopPropagation();
                this.dropZoneActive = false;
                const files = e.dataTransfer.files;
                this.uploadingCount = files.length;
                for (let i = 0; i < files.length; i++) {
                    if ($wire.get('attachments').length >= this.maxFiles) break;
                    const done = () => { this.uploadingCount = Math.max(0, this.uploadingCount - 1); };
                    $wire.upload('attachments', files[i], done, done, () => {});
                }
            },
            handleFileSelect(e) {
                if (typeof $wire === 'undefined') return;
                const files = e.target.files;
                this.uploadingCount = files.length;
                for (let i = 0; i < files.length; i++) {
                    if ($wire.get('attachments').length >= this.maxFiles) break;
                    const done = () => { this.uploadingCount = Math.max(0, this.uploadingCount - 1); };
                    $wire.upload('attachments', files[i], done, done, () => {});
                }
                e.target.value = '';
            }
        }"
        @click.outside="expanded = false"
    >
        <form
            wire:submit.prevent="createItem"
            @submit.capture="if (uploadingCount > 0) { $event.preventDefault(); $event.stopImmediatePropagation(); }"
        >
            <flux:field @focus.capture="expanded = true">
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

            <div
                class="feed-composer-expandable mt-4 space-y-4"
                x-show="expanded"
                x-cloak
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
            >
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
                    @if($useSinglePerRequestUpload)
                    <div
                        data-feed-drop-zone-single="feed-composer-attachments"
                        class="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 transition dark:border-zinc-600"
                        :class="{ 'border-primary-500 bg-primary-500/10 ring-2 ring-primary-400/50 dark:border-primary-500 dark:bg-primary-500/20': dropZoneActive, 'ring-2 ring-amber-400/50 dark:ring-amber-400/50': uploadingCount > 0 }"
                        role="button"
                        tabindex="0"
                        aria-label="{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}"
                        @dragenter.prevent="dropZoneActive = true"
                        @dragleave.prevent="if (!$event.currentTarget.contains($event.relatedTarget)) dropZoneActive = false"
                        @drop.prevent="handleDrop($event)"
                        @dragover.prevent
                        @click="$refs.attachmentsInput.click()"
                        @keydown.enter.prevent="$refs.attachmentsInput.click()"
                        @keydown.space.prevent="$refs.attachmentsInput.click()"
                    >
                        <span class="text-center text-sm text-zinc-600 dark:text-zinc-400" x-show="uploadingCount === 0">{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}</span>
                        <span class="text-center text-sm text-amber-600 dark:text-amber-400" x-show="uploadingCount > 0" x-cloak>{{ __('filament-social-graph::feed.uploading_files') }}</span>
                    </div>
                    <input
                        type="file"
                        x-ref="attachmentsInput"
                        id="feed-composer-attachments"
                        class="sr-only"
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                        @change="handleFileSelect($event)"
                    >
                    @else
                    <div
                        class="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 transition dark:border-zinc-600"
                        :class="{ 'border-primary-500 bg-primary-500/10 ring-2 ring-primary-400/50 dark:border-primary-500 dark:bg-primary-500/20': dropZoneActive }"
                        data-feed-drop-zone="feed-composer-attachments"
                        onclick="document.getElementById('feed-composer-attachments').click()"
                        role="button"
                        tabindex="0"
                        aria-label="{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}"
                        onkeydown="if(event.key==='Enter'||event.key===' ') { event.preventDefault(); document.getElementById('feed-composer-attachments').click(); }"
                        @dragenter.prevent="dropZoneActive = true"
                        @dragleave.prevent="if (!$event.currentTarget.contains($event.relatedTarget)) dropZoneActive = false"
                    >
                        <span class="text-center text-sm text-zinc-600 dark:text-zinc-400">{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}</span>
                    </div>
                    <input
                        type="file"
                        id="feed-composer-attachments"
                        wire:model="attachments"
                        class="sr-only"
                        multiple
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                    >
                    @endif
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
                <span
                    class="inline-block"
                    :class="{ 'pointer-events-none opacity-50 cursor-not-allowed': uploadingCount > 0 }"
                    @click.capture="if (uploadingCount > 0) $event.preventDefault(); $event.stopPropagation(); $event.stopImmediatePropagation()"
                >
                    <flux:button
                        type="submit"
                        variant="primary"
                        size="base"
                        wire:loading.attr="disabled"
                        wire:target="createItem,attachments"
                        class="inline-flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="createItem" x-show="uploadingCount === 0" x-transition>{{ __('filament-social-graph::feed.post') }}</span>
                        <span wire:loading wire:target="createItem" x-show="uploadingCount === 0" x-transition class="inline-flex items-center gap-2">
                            {{ __('filament-social-graph::feed.posting') }}
                            <svg class="animate-spin size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span x-show="uploadingCount > 0" x-cloak x-transition class="inline-flex items-center gap-2">
                            {{ __('filament-social-graph::feed.uploading_files') }}
                            <svg
                                class="animate-spin size-4 shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </flux:button>
                </span>
            </div>
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
    });

    document.addEventListener('livewire:updated', () => {
        initQuill(@json($quillId));
    });
})();
</script>
@endpush

@include('filament-social-graph::feed.partials.attachment-drop-zone-script')
