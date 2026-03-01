<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-2 flex items-center gap-2">
        <span class="font-medium text-gray-900 dark:text-white">
            {{ $feedItem->actor?->name ?? class_basename($feedItem->actor_type) }}
        </span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $feedItem->created_at->diffForHumans() }}
        </span>
        @if($feedItem->visibility)
            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                {{ $feedItem->visibility->label() }}
            </span>
        @endif
    </div>

    @if($feedItem->subject)
        <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">
            {{ $feedItem->subject }}
        </h3>
    @endif

    @if($feedItem->body)
        <div class="prose prose-sm dark:prose-invert max-w-none">
            {!! \Illuminate\Support\Str::markdown($feedItem->body) !!}
        </div>
    @endif

    @php
        $attachments = $feedItem->attachments ?? [];
        $disk = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::getStorageDisk();
    @endphp
    @if(!empty($attachments))
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($attachments as $path)
                @php
                    $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
                    $filename = basename($path);
                    $isImage = \BeegoodIT\FilamentSocialGraph\Models\FeedItem::isImagePath($path);
                @endphp
                @if($isImage)
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $url }}" alt="{{ $filename }}" class="max-h-48 rounded object-cover">
                    </a>
                @else
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        {{ $filename }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
