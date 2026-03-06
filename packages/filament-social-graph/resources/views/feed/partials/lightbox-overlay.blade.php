<div
    id="fsg-lightbox"
    hidden
    class="fixed inset-0 z-[110] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('filament-social-graph::feed_item.lightbox_label') }}"
>
    <div
        data-fsg-lightbox-backdrop
        class="absolute inset-0 bg-black/70 backdrop-blur-sm"
        aria-hidden="true"
    ></div>
    <button
        type="button"
        data-fsg-lightbox-prev
        aria-label="{{ __('filament-social-graph::feed_item.lightbox_prev') }}"
        class="absolute left-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-800 shadow hover:bg-white dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
        hidden
    >
        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <button
        type="button"
        data-fsg-lightbox-next
        aria-label="{{ __('filament-social-graph::feed_item.lightbox_next') }}"
        class="absolute right-2 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-800 shadow hover:bg-white dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
        hidden
    >
        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
    <div class="relative z-10 flex max-h-full max-w-full flex-col items-center gap-3">
        <img
            data-fsg-lightbox-image
            src=""
            alt=""
            class="max-h-[85vh] max-w-full rounded object-contain"
        >
        <div class="flex flex-wrap items-center justify-center gap-2">
            <button
                type="button"
                data-fsg-lightbox-close
                class="rounded bg-white px-3 py-1.5 text-sm font-medium text-gray-900 shadow hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
            >
                {{ __('filament-social-graph::feed_item.lightbox_close') }}
            </button>
        </div>
    </div>
</div>
