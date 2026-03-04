@extends($layout)

@section('content')
<div>
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $title }}
        </h1>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <form method="POST" action="{{ $updateUrl }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div>
                <flux:field>
                    <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.subject') }}</flux:label>
                    <flux:input
                        type="text"
                        name="subject"
                        id="feed-edit-subject"
                        value="{{ old('subject', $feedItem->subject) }}"
                        placeholder="{{ __('filament-social-graph::feed_item.subject') }}"
                    />
                </flux:field>
            </div>
            <div>
                <flux:field>
                    <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.body') }}</flux:label>
                    <flux:textarea
                        name="body"
                        id="feed-edit-body"
                        rows="3"
                        placeholder="{{ __('filament-social-graph::feed.composer_placeholder') }}"
                    >{{ old('body', $feedItem->body) }}</flux:textarea>
                    @error('body')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:field>
                    <flux:label class="sr-only">{{ __('filament-social-graph::feed_item.visibility') }}</flux:label>
                    <flux:select name="visibility" class="min-w-[10rem]" value="{{ old('visibility', $feedItem->visibility?->value ?? \BeegoodIT\FilamentSocialGraph\Enums\Visibility::Public->value) }}">
                        @foreach (\BeegoodIT\FilamentSocialGraph\Enums\Visibility::cases() as $v)
                            <flux:select.option :value="$v->value">{{ $v->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('visibility')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
                @php
                    $existingAttachments = $feedItem->attachments ?? [];
                    $editDisk = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::getStorageDisk();
                @endphp
                @if(!empty($existingAttachments))
                    <div>
                        <flux:field>
                            <flux:label>{{ __('filament-social-graph::feed_item.attachments') }}</flux:label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($existingAttachments as $path)
                                    @php
                                        $url = \Illuminate\Support\Facades\Storage::disk($editDisk)->url($path);
                                        $filename = basename($path);
                                        $isImage = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::isImagePath($path);
                                    @endphp
                                    <label class="flex items-start gap-2 rounded border border-zinc-200 p-2 dark:border-zinc-600">
                                        <input type="checkbox" name="attachments_remove[]" value="{{ e($path) }}" class="rounded" />
                                        @if($isImage)
                                            <a href="{{ $url }}" target="_blank" rel="noopener" class="block shrink-0">
                                                <img src="{{ $url }}" alt="{{ $filename }}" class="max-h-24 rounded object-cover">
                                            </a>
                                        @else
                                            <a href="{{ $url }}" target="_blank" rel="noopener" class="text-sm text-zinc-600 dark:text-zinc-400">{{ $filename }}</a>
                                        @endif
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('filament-social-graph::feed_item.attachments_remove') }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </flux:field>
                    </div>
                @endif
                <div>
                    <flux:field>
                        <flux:label for="feed-edit-attachments">{{ __('filament-social-graph::feed_item.attachments_new') }}</flux:label>
                        <flux:input type="file" name="attachments[]" id="feed-edit-attachments" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" />
                        <flux:description>{{ __('filament-social-graph::feed_item.attachments_hint', ['max_files' => config('filament-social-graph.attachments.max_files', 5), 'max_mb' => (int) (config('filament-social-graph.attachments.max_file_size_kb', 5120) / 1024)]) }}</flux:description>
                        @error('attachments')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                        @error('attachments.*')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </flux:field>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $feedUrl }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        {{ __('filament-social-graph::feed.cancel') }}
                    </a>
                    <flux:button type="submit" variant="primary" size="base">
                        {{ __('filament-social-graph::feed.update') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
