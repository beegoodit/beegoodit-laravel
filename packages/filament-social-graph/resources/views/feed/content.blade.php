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
                        <flux:textarea
                            name="body"
                            id="feed-body"
                            rows="3"
                            placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"
                        >{{ old('body') }}</flux:textarea>
                        @error('body')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <flux:field>
                        <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.visibility') }}</flux:label>
                        <flux:select name="visibility" class="min-w-[10rem]" value="{{ old('visibility', \BeegoodIT\FilamentSocialGraph\Enums\Visibility::Public->value) }}">
                            @foreach (\BeegoodIT\FilamentSocialGraph\Enums\Visibility::cases() as $v)
                                <flux:select.option :value="$v->value">{{ $v->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('visibility')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                    <div>
                        <flux:field>
                            <flux:label for="feed-attachments">{{ __('filament-social-graph::feed_item.attachments') }}</flux:label>
                            <flux:input type="file" name="attachments[]" id="feed-attachments" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" />
                            <flux:description>{{ __('filament-social-graph::feed_item.attachments_hint', ['max_files' => config('filament-social-graph.attachments.max_files', 5), 'max_mb' => (int) (config('filament-social-graph.attachments.max_file_size_kb', 5120) / 1024)]) }}</flux:description>
                            @error('attachments')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                            @error('attachments.*')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>
                    <flux:button type="submit" variant="primary" size="base">
                        {{ __('filament-social-graph::feed.post') }}
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <div class="mt-6">
        @livewire(\BeegoodIT\FilamentSocialGraph\Livewire\FeedList::class, [
            'entityType' => $entity->getMorphClass(),
            'entityId' => $entity->getKey(),
        ])
    </div>
</div>
