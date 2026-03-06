<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-2 flex flex-wrap items-center gap-2">
        <span class="font-medium text-gray-900 dark:text-white">
            {{ $feedItem->actor?->name ?? class_basename($feedItem->actor_type) }}
        </span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $feedItem->created_at->diffForHumans() }}
        </span>
        @php
            $editUrlResolver = config('filament-social-graph.feed_page.feed_item_edit_url_resolver');
            $destroyUrlResolver = config('filament-social-graph.feed_page.feed_item_destroy_url_resolver');
        @endphp
        <div class="ml-auto flex items-center gap-2">
            @if($editUrlResolver && \Illuminate\Support\Facades\Gate::allows('update', $feedItem))
                @php $editUrl = $editUrlResolver($feedItem); @endphp
                @if($editUrl)
                    <flux:button href="{{ $editUrl }}" variant="outline" size="sm">
                        {{ __('filament-social-graph::feed.edit') }}
                    </flux:button>
                @endif
            @endif
            @if($destroyUrlResolver && \Illuminate\Support\Facades\Gate::allows('delete', $feedItem))
                @php $destroyUrl = $destroyUrlResolver($feedItem); @endphp
                @if($destroyUrl)
                    <form method="POST" action="{{ $destroyUrl }}" class="inline" x-data x-on:submit="if (!confirm($el.getAttribute('data-confirm'))) $event.preventDefault()" data-confirm="{{ e(__('filament-social-graph::feed_item.delete_confirm')) }}">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger" size="sm">
                            {{ __('filament-social-graph::feed_item.delete') }}
                        </flux:button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    @if($feedItem->subject)
        <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">
            {{ $feedItem->subject }}
        </h3>
    @endif

    @if($feedItem->body)
        @php
            $body = $feedItem->body;
            $isHtml = str_contains($body, '<') && str_contains($body, '>');
            if ($isHtml) {
                $body = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $body);
                $allowedTags = '<p><br><strong><em><b><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote>';
                $rendered = strip_tags($body, $allowedTags);
            } else {
                $rendered = \Illuminate\Support\Str::markdown($body);
            }
        @endphp
        <div class="prose prose-sm dark:prose-invert max-w-none">
            {!! $rendered !!}
        </div>
    @endif

    @php
        if (! isset($imageEntries)) {
            $attachments = $feedItem->attachments ?? [];
            $disk = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::getStorageDisk();
            $imagePaths = array_values(array_filter($attachments, \BeegoodIT\FilamentSocialGraph\Models\FeedItem::isImagePath(...)));
            $filePaths = array_values(array_filter($attachments, fn (string $path): bool => ! \BeegoodIT\FilamentSocialGraph\Models\FeedItem::isImagePath($path)));
            $imageEntries = array_map(fn (string $path): array => [
                'path' => $path,
                'url' => \Illuminate\Support\Facades\Storage::disk($disk)->url($path),
                'filename' => basename($path),
            ], $imagePaths);
            $fileEntries = array_map(fn (string $path): array => [
                'path' => $path,
                'url' => \Illuminate\Support\Facades\Storage::disk($disk)->url($path),
                'filename' => basename($path),
            ], $filePaths);
            $imageGridClass = count($imagePaths) <= 1 ? 'grid grid-cols-1 max-w-2xl' : (count($imagePaths) <= 4 ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2');
        }
        $hasAttachments = ! empty($imageEntries) || ! empty($fileEntries);
    @endphp
    @if($hasAttachments)
        @if(! empty($imageEntries))
            <div class="mt-3 {{ $imageGridClass }}" data-lightbox-group>
                @foreach($imageEntries as $entry)
                    <a
                        data-lightbox
                        href="{{ $entry['url'] }}"
                        class="block overflow-hidden rounded-lg border border-gray-200 shadow-sm transition hover:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:border-gray-600 dark:focus:ring-gray-500"
                    >
                        <span class="block aspect-video w-full">
                            <img
                                src="{{ $entry['url'] }}"
                                alt="{{ $entry['filename'] }}"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            >
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
        @if(! empty($fileEntries))
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($fileEntries as $entry)
                    <a
                        href="{{ $entry['url'] }}"
                        target="_blank"
                        rel="noopener"
                        class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        {{ $entry['filename'] }}
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>
