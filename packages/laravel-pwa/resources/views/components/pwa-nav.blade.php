@props([
    'items' => [],
    'menuTitle' => 'Menu',
])

@php
    $items = is_array($items) ? $items : (is_callable($items) ? $items() : []);
    $paddingBottom = config('pwa.navigation.padding_bottom', '4rem');
    $activeClass = config('pwa.navigation.active_color_class', 'text-amber-500');
    $barClass = config('pwa.navigation.bar_class', 'bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-t border-gray-200/50 dark:border-gray-800/50 shadow-[0_-1px_10px_rgba(0,0,0,0.05)]');
    $barItemInactiveClass = config('pwa.navigation.bar_item_inactive_class', 'text-gray-500 dark:text-gray-400');
    $barItemHoverClass = config('pwa.navigation.bar_item_hover_class', 'group-hover:text-amber-400');
    $barItemInactiveHoverClass = trim($barItemInactiveClass.' '.$barItemHoverClass);
    $sheetBackdropClass = config('pwa.navigation.sheet_backdrop_class', 'bg-gray-500/75 dark:bg-gray-900/75');
    $sheetPanelClass = config('pwa.navigation.sheet_panel_class', 'bg-white dark:bg-gray-900 rounded-t-2xl shadow-xl');
    $sheetHeaderBorderClass = config('pwa.navigation.sheet_header_border_class', 'border-gray-200 dark:border-gray-800');
    $sheetTitleClass = config('pwa.navigation.sheet_title_class', 'text-gray-900 dark:text-white');
    $sheetCloseClass = config('pwa.navigation.sheet_close_class', 'text-gray-400 hover:text-gray-500 dark:hover:text-gray-300');
@endphp

<style>
    #pwa-navigation-bar {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
    }
    main,
    .fi-main,
    .fi-sidebar {
        padding-bottom: {{ $paddingBottom }} !important;
    }
</style>

<div x-data="{ mobileMenuOpen: false }">
    <div
        id="pwa-navigation-bar"
        class="fixed bottom-0 inset-x-0 z-[25] {{ $barClass }}"
        style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    >
        <nav class="flex justify-around items-center h-16 w-full max-w-lg mx-auto">
            @foreach ($items as $item)
                @php
                    $isActive = $item['active'] ?? false;
                    $isMenu = isset($item['action']) && $item['action'] === 'toggleMenu';
                @endphp

                @if ($isMenu)
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex flex-col items-center justify-center flex-1 h-full min-w-0 px-0.5 group focus:outline-none shrink-0"
                    >
                        <div
                            class="relative flex items-center justify-center transition-colors duration-200 shrink-0"
                            :class="mobileMenuOpen ? '{{ $activeClass }}' : '{{ $barItemInactiveHoverClass }}'"
                        >
                            @include('laravel-pwa::components.icon', ['icon' => $item['icon'] ?? 'heroicon-o-bars-3', 'class' => 'w-5 h-5'])
                        </div>
                        <span
                            class="mt-1 text-[8px] sm:text-[10px] uppercase font-bold tracking-tighter transition-colors duration-200 truncate w-full text-center {{ $barItemInactiveHoverClass }}"
                            :class="mobileMenuOpen ? '{{ $activeClass }}' : ''"
                        >
                            {{ $item['label'] ?? $menuTitle }}
                        </span>
                    </button>
                @else
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="flex flex-col items-center justify-center flex-1 h-full min-w-0 px-0.5 group shrink-0"
                    >
                        <div class="relative flex items-center justify-center shrink-0 {{ $isActive ? $activeClass : $barItemInactiveHoverClass }}">
                            @include('laravel-pwa::components.icon', [
                                'icon' => $item['icon'] ?? 'heroicon-o-home',
                                'class' => 'w-5 h-5 transition-colors duration-200',
                            ])
                        </div>
                        <span
                            class="mt-1 text-[8px] sm:text-[10px] uppercase font-bold tracking-tighter transition-colors duration-200 truncate w-full text-center {{ $isActive ? $activeClass : $barItemInactiveHoverClass }}"
                        >
                            {{ $item['label'] ?? '' }}
                        </span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>

    <div
        x-show="mobileMenuOpen"
        x-cloak
        class="relative z-[50]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="pwa-nav-sheet-title"
    >
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 {{ $sheetBackdropClass }} transition-opacity"
            @click="mobileMenuOpen = false"
        ></div>

        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="fixed inset-x-0 bottom-0 z-[51] w-full {{ $sheetPanelClass }} overflow-hidden"
            style="margin-bottom: calc({{ $paddingBottom }} + env(safe-area-inset-bottom, 0px));"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b {{ $sheetHeaderBorderClass }}">
                <span id="pwa-nav-sheet-title" class="text-lg font-bold {{ $sheetTitleClass }}">{{ $menuTitle }}</span>
                <button
                    type="button"
                    class="{{ $sheetCloseClass }} p-1"
                    @click="mobileMenuOpen = false"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-4 py-4 space-y-4 overflow-y-auto max-h-[70vh]">
                {{ $menu ?? $slot }}
            </div>
        </div>
    </div>
</div>
