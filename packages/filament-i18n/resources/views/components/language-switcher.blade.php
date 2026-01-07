@php
    $localeMetadata = config('filament-i18n.locales', []);
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false" type="button"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
        aria-label="Language options">
        <span class="text-xs font-bold uppercase">{{ $currentLocale }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-40 origin-top-right bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
        style="display: none;">
        <div class="py-1">
            @foreach($locales as $locale)
                @php
                    $metadata = $localeMetadata[$locale] ?? ['native' => strtoupper($locale), 'flag' => ''];
                    $routeName = $locale . '.' . ($routeBase ?? 'home');
                    $url = \Route::has($routeName) ? route($routeName, request()->route()?->parameters() ?? []) : '/' . $locale;
                @endphp
                <a href="{{ $url }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $locale === $currentLocale ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    @if(!empty($metadata['flag']))
                        <span>{{ $metadata['flag'] }}</span>
                    @endif
                    <span>{{ $metadata['native'] ?? strtoupper($locale) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>