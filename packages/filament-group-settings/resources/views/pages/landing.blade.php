<x-filament-panels::page>
    @php
        $pages = $this->getLinkedPages();
    @endphp

    @if (count($pages) === 0)
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('filament-group-settings::messages.No group settings') }}
        </p>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($pages as $page)
                <li>
                    <a
                        href="{{ $page['url'] }}"
                        class="block rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <span class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $page['label'] }}
                        </span>
                        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                            {{ __('filament-group-settings::messages.Settings') }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-filament-panels::page>
