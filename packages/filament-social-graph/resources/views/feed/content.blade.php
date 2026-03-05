<div>
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $entity->name ?? class_basename($entity) }} - {{ __('filament-social-graph::feed.title') }}
        </h1>
    </div>

    @if($showComposer)
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form method="POST" action="{{ url()->current() }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div>
                    <flux:field>
                        <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.subject') }}</flux:label>
                        <flux:input
                            type="text"
                            name="subject"
                            id="feed-subject"
                            value="{{ old('subject') }}"
                            placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                        />
                    </flux:field>
                </div>
                <div>
                    <flux:field>
                        <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.body') }}</flux:label>
                        <div id="feed-body-editor" class="min-h-[100px] rounded-lg border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-800" data-placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"></div>
                        <input type="hidden" name="body" id="feed-body" value="{{ old('body') }}">
                        @error('body')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>
                <div>
                    <div
                        x-data="feedAttachmentPreview({{ config('filament-social-graph.attachments.max_files', 5) }})"
                        class="contents"
                    >
                        <flux:field>
                            <flux:label for="feed-attachments">{{ __('filament-social-graph::feed_item.attachments') }}</flux:label>
                            <div
                                role="button"
                                tabindex="0"
                                aria-label="{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}"
                                class="flex min-h-[7.5rem] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 transition dark:border-zinc-600"
                                :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': isDragging }"
                                @click="$refs.input.click()"
                                @keydown.enter.prevent="$refs.input.click()"
                                @keydown.space.prevent="$refs.input.click()"
                                @dragover.prevent="onDragover($event)"
                                @dragleave.prevent="onDragleave($event)"
                                @drop.prevent="onDrop($event)"
                            >
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('filament-social-graph::feed_item.attachments_drop_placeholder') }}</span>
                            </div>
                            <input
                                type="file"
                                name="attachments[]"
                                id="feed-attachments"
                                x-ref="input"
                                class="sr-only"
                                multiple
                                accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                                @change="onChange($event)"
                            />
                            <flux:description>{{ __('filament-social-graph::feed_item.attachments_hint', ['max_files' => config('filament-social-graph.attachments.max_files', 5), 'max_mb' => (int) (config('filament-social-graph.attachments.max_file_size_kb', 5120) / 1024)]) }}</flux:description>
                            @error('attachments')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                            @error('attachments.*')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                            <div x-show="files.length" class="mt-2 flex flex-wrap gap-2" x-cloak>
                                <template x-for="(item, index) in files" :key="index">
                                    <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-600 dark:bg-zinc-700">
                                        <img x-show="item.preview" :src="item.preview" class="h-12 w-12 rounded object-cover" alt="">
                                        <span x-show="!item.preview" class="flex h-12 w-12 items-center justify-center rounded bg-zinc-200 text-zinc-500 dark:bg-zinc-600 dark:text-zinc-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </span>
                                        <span class="max-w-[8rem] truncate text-sm text-zinc-700 dark:text-zinc-300" x-text="item.name"></span>
                                    </div>
                                </template>
                            </div>
                        </flux:field>
                    </div>
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" size="base">
                        {{ __('filament-social-graph::feed.post') }}
                    </flux:button>
                </div>
            </form>
        </div>
        @push('styles')
            <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
            @include('filament-social-graph::feed.partials.quill-dark-mode')
        @endpush
        @push('scripts')
            <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var editorEl = document.getElementById('feed-body-editor');
                    var hiddenEl = document.getElementById('feed-body');
                    if (!editorEl || !hiddenEl) return;
                    var quill = new Quill(editorEl, {
                        theme: 'snow',
                        placeholder: editorEl.getAttribute('data-placeholder') || '',
                    });
                    if (hiddenEl.value) quill.root.innerHTML = hiddenEl.value;
                    quill.on('text-change', function() { hiddenEl.value = quill.root.innerHTML; });
                });
            </script>
            <script>
                document.addEventListener('alpine:init', function() {
                    Alpine.data('feedAttachmentPreview', function(maxFiles) {
                        return {
                            files: [],
                            isDragging: false,
                            maxFiles: typeof maxFiles === 'number' ? maxFiles : 5,
                            onChange: function(event) {
                                var input = event.target;
                                this.files = [];
                                for (var i = 0; i < input.files.length; i++) {
                                    var file = input.files[i];
                                    var item = { name: file.name, preview: null };
                                    this.files.push(item);
                                    if (file.type.indexOf('image/') === 0) {
                                        var reader = new FileReader();
                                        reader.onload = function(e) {
                                            item.preview = e.target.result;
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            },
                            onDragover: function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                this.isDragging = true;
                            },
                            onDragleave: function(e) {
                                e.preventDefault();
                                this.isDragging = false;
                            },
                            onDrop: function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                this.isDragging = false;
                                var dt = e.dataTransfer;
                                if (!dt || !dt.files || dt.files.length === 0) return;
                                var input = this.$refs.input;
                                var existing = input.files ? Array.from(input.files) : [];
                                var dropped = Array.from(dt.files);
                                var combined = existing.concat(dropped).slice(0, this.maxFiles);
                                var dataTransfer = new DataTransfer();
                                combined.forEach(function(file) { dataTransfer.items.add(file); });
                                input.files = dataTransfer.files;
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            },
                        };
                    });
                });
            </script>
        @endpush
    @endif

    <div class="mt-6">
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
    </div>
</div>
