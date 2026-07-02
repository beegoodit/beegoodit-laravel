@php
    $localeMetadata = config('filament-i18n.locales', []);
@endphp

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        @click.outside="open = false"
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-2 text-sm font-medium uppercase text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 dark:focus-visible:ring-zinc-500"
        aria-label="{{ __('filament-i18n::messages.locale') }}"
    >
        <span class="text-xs font-bold">{{ $currentLocale }}</span>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-44 origin-top-right rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="py-1">
            @foreach ($locales as $locale)
                @php
                    $metadata = $localeMetadata[$locale] ?? ['native' => strtoupper($locale), 'flag' => ''];
                    $routeName = $locale.'.'.($routeBase ?? 'home');
                    $url = \Route::has($routeName) ? route($routeName, request()->route()?->parameters() ?? []) : '/'.$locale;
                @endphp
                <a
                    href="{{ $url }}"
                    @class([
                        'flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800',
                        'bg-zinc-100 dark:bg-zinc-800' => $locale === $currentLocale,
                    ])
                >
                    @if (! empty($metadata['flag']))
                        <span aria-hidden="true">{{ $metadata['flag'] }}</span>
                    @endif
                    <span>{{ $metadata['native'] ?? strtoupper($locale) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
